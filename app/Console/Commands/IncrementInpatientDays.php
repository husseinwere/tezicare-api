<?php

namespace App\Console\Commands;

use App\Models\Patient\PatientSession;
use App\Models\Patient\WardRound;
use App\Models\Queues\InpatientQueue;
use App\Models\Ward\Bed;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class IncrementInpatientDays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:increment-inpatient-days';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command adds a day to the inpatient days of all inpatients by adding a ward round record to their session';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::channel('scheduler')->info('app:increment-inpatient-days command is running.');

        $inpatients = InpatientQueue::with('session')
                                    ->whereHas('session', function($q) {
                                        $q->where('discharged', NULL);
                                    })
                                    ->where('status', 'ACTIVE')->get();

        foreach($inpatients as $inpatient){
            $bed = Bed::find($inpatient->bed_id);
            $session = PatientSession::with('consultation')->find($inpatient->session_id);

            $wardRound = new WardRound();
            $wardRound->hospital_id = $session->hospital_id;
            $wardRound->session_id = $inpatient->session_id;
            $wardRound->bed_id = $inpatient->bed_id;
            $wardRound->bed_price = $bed->ward->price;
            $wardRound->nurse_price = $session->consultation->inpatient_nurse_rate;
            $wardRound->doctor_price = $session->consultation->inpatient_doctor_rate;
            $wardRound->created_by = 1;
            $wardRound->save();
        }
    }
}

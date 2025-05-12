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
            $bed = Bed::with(['ward.prices'])->find($inpatient->bed_id);
            $session = PatientSession::with('consultation.prices')->find($inpatient->session_id);

            $bedPrice = $bed->ward->price;
            $wardPrices = $bed->ward->prices;
            $nursePrice = $session->consultation->inpatient_nurse_rate;
            $doctorPrice = $session->consultation->inpatient_doctor_rate;
            $consultationPrices = $session->consultation->prices;

            if($session->insurance_id) {
                $wardInsurancePrice = $wardPrices->where('insurance_id', $session->insurance_id)->first();
                if($wardInsurancePrice) {
                    $bedPrice = $wardInsurancePrice['price'];
                }

                $consultationInsurancePrice = $consultationPrices->where('insurance_id', $session->insurance_id)->first();
                if($consultationInsurancePrice) {
                    if($consultationInsurancePrice['inpatient_doctor_price']) { $doctorPrice = $consultationInsurancePrice['inpatient_doctor_price']; }
                    if($consultationInsurancePrice['inpatient_nurse_price']) { $nursePrice = $consultationInsurancePrice['inpatient_nurse_price']; }
                }
            }

            $wardRound = new WardRound();
            $wardRound->hospital_id = $inpatient->hospital_id;
            $wardRound->session_id = $inpatient->session_id;
            $wardRound->bed_id = $inpatient->bed_id;
            $wardRound->bed_price = $bedPrice;
            $wardRound->nurse_price = $nursePrice;
            $wardRound->doctor_price = $doctorPrice;
            $wardRound->created_by = 1;
            $wardRound->save();
        }
    }
}

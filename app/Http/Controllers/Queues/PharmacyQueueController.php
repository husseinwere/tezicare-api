<?php

namespace App\Http\Controllers\Queues;

use App\Http\Controllers\Controller;
use App\Models\Queues\PharmacyQueue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PharmacyQueueController extends Controller
{
    protected $table = 'pharmacy_queue';

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->query('page_size', 20);
        $pageIndex = $request->query('page_index', 1);

        $queue = DB::table($this->table)
                    ->join('patient_sessions', $this->table . '.session_id', '=', 'patient_sessions.id')
                    ->join('patients', 'patient_sessions.patient_id', '=', 'patients.id')
                    ->select('patients.first_name', 'patients.last_name', 'patients.gender', 'patients.dob')
                    ->paginate($pageSize, ['*'], 'page', $pageIndex);
        
        return $queue;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //DELETE FROM PREVIOUS QUEUE WHERE NECESSARY

        //SAVE
        $request->validate([
            'session_id' => 'required'
        ]);
        $data = $request->all();
        $data['created_by'] = Auth::id();

        return PharmacyQueue::create($data);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return DB::table($this->table)
        ->join('patient_sessions', $this->table . '.session_id', '=', 'patient_sessions.id')
        ->join('patients', 'patient_sessions.patient_id', '=', 'patients.id')
        ->where($this->table . '.id', $id)
        ->select('patients.first_name', 'patients.last_name', 'patients.gender', 'patients.dob')
        ->first();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->all();

        $queue = PharmacyQueue::find($id);
        $queue->update($data);

        return $queue;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return PharmacyQueue::destroy($id);
    }
}

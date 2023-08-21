<?php

namespace App\Http\Controllers\Queues;

use App\Http\Controllers\Controller;
use App\Models\Queues\TriageQueue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TriageQueueController extends Controller
{
    protected $table = 'triage_queue';

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
                    ->join('users', $this->table . '.created_by', '=', 'users.id')
                    ->select($this->table . '.status', $this->table . '.created_at', 
                                DB::raw('CONCAT(users.first_name, " ", users.last_name) as created_by'), 
                                DB::raw('CONCAT(patients.first_name, " ", patients.last_name) as patient_name'),
                                'patients.gender', 'patients.dob')
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

        return TriageQueue::create($data);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return DB::table($this->table)
                    ->join('patient_sessions', $this->table . '.session_id', '=', 'patient_sessions.id')
                    ->join('patients', 'patient_sessions.patient_id', '=', 'patients.id')
                    ->join('users', $this->table . '.created_by', '=', 'users.id')
                    ->where($this->table . '.id', $id)
                    ->select($this->table . '.status', $this->table . '.created_at', 
                                DB::raw('CONCAT(users.first_name, " ", users.last_name) as created_by'), 
                                DB::raw('CONCAT(patients.first_name, " ", patients.last_name) as patient_name'),
                                'patients.gender', 'patients.dob')
                    ->first();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->all();

        $queue = TriageQueue::find($id);
        $queue->update($data);

        return $queue;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return TriageQueue::destroy($id);
    }
}

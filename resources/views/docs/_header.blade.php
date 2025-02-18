<tr class='top_rw'>
    <td colspan='2'>
        <h2 style='margin-bottom: 0px;'> {{ $title }} </h2>
        <span> Admission: {{ $patientSession->created_at }} </span> <br>
        <span> Discharge: {{ $patientSession->discharged }} </span>
    </td>
    <td  style='width:30%; margin-right: 10px;'>
        <img class="logo" src="{{ str_replace(asset(''), '', $hospital->logo) }}">
    </td>
</tr>
<tr class='information'>
    <td colspan='3'>
        <table>
            <thead>
                <tr>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan='2'>
                        <b> Patient </b> <br>
                        {{ $patientSession->patient->first_name }} {{ $patientSession->patient->last_name }} (OP No: {{ $patientSession->patient->id }}) <br>
                        Gender: {{ $patientSession->patient->gender }}, Age: {{ $patientSession->patient->age }}<br>
                        {{ $patientSession->patient->phone }}, {{ $patientSession->patient->email }}<br>
                        {{ $patientSession->patient->residence }}
                    </td>
                    <td>
                        <b> {{ $hospital->name }} </b> <br>
                        {{ $hospital->address }}<br>
                        {{ $hospital->email }}<br>
                        {{ $hospital->phone }}
                    </td>
                </tr>
            </tbody>
        </table>
    </td>
</tr>
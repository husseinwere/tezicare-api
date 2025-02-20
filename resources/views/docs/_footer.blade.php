<tr>
    <td colspan='3'>
        <table cellspacing='0px' cellpadding='2px'>
            <thead>
                <tr>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td width='50%'>
                    </td>
                    <td>
                    * This is a computer generated document and does not
                    require a physical signature
                    </td>
                </tr>
                <tr>
                    <td width='50%'>
                    </td>
                    <td>
                        <b> Doctor In Charge </b>
                        <br>
                        @if($patientSession->doctor)
                            {{ $patientSession->doctor->first_name }} {{ $patientSession->doctor->last_name }}
                        @else
                            N/A
                        @endif
                        <br><br>
                        ...................................
                        <img class="stamp" src="{{ str_replace(asset(''), '', $hospital->stamp) }}">
                        <br>
                        <br>
                        <br>
                    </td>
                </tr>
            </tbody>
        </table>
    </td>
</tr>
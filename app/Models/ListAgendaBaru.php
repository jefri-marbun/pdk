<?php

namespace silatng\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ListAgendaBaru extends Model
{
    public function getStoredProcValue($no_agenda_baru, $nomor_nib)
    {
        // Adjust the procedure name and parameters according to your specific case
        $result = DB::select('CALL sp_mig_nomor_agenda_baru(?, ?)', [$no_agenda_baru, $nomor_nib]);

        // Process the result if needed
        // For example, assuming the procedure returns a single row with a 'value' column:
        if (!empty($result) && isset($result[0]->value)) {
            return $result[0]->value;
        }

        // Return a default value if no result or value is found
        return null;
    }
}

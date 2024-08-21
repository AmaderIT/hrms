<?php

namespace App\Http\Requests\internalTransfer;

use App\Models\InternalTransfer;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RequestInternalTransfer extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "source_warehouse_id"       => "required_without:source_department_id",
            "source_department_id"      => "required_without:source_warehouse_id",
            "destination_warehouse_id"  => "required_without_all:destination_department_id,to_supplier_id,to_supplier_name",
            "destination_department_id" => "required_without_all:destination_warehouse_id,to_supplier_id,to_supplier_name",
            "to_supplier_id"            => "required_without_all:destination_warehouse_id,destination_department_id,to_supplier_name",
            "to_supplier_name"          => "required_without_all:destination_warehouse_id,destination_department_id,to_supplier_id",
            "is_returnable"             => "required",
            "issue_at"                  => "required",
            "delivered_by"              => "required",
            "search"                    => "required|array",
            "qty"                       => "required|array",
            "qty.*"                     => "required|numeric|min:1",
            "remarks"                   => "nullable|array",
            "remarks.*"                 => "nullable|string",
            "unit"                      => "nullable|array",
            "unit.*"                    => "nullable|string",
            "file"                      => "nullable|file|mimes:jpeg,png,jpg,pdf,xlsx,docx,doc|max:5120",
            "reference"                 => "nullable|string|min:3|max:100",
        ];
    }

    //'attachment' => [
    //        'required',
    //        File::types(['mp3', 'wav'])
    //            ->min(1024)
    //            ->max(12 * 1024),
    //    ],

}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentoController extends Controller
{
    public function baixar($id)
    {
        // colocar middleware role:dono,gerente em rota ou aqui verificar Auth::user role
        $doc = DB::table('documentos')->where('id', $id)->first();
        if(!$doc) abort(404);

        if(!Storage::disk('local')->exists($doc->arquivo_url)) abort(404);
        return Storage::disk('local')->download($doc->arquivo_url);
    }
}

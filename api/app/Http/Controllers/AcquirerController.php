<?php

namespace App\Http\Controllers;

use App\Models\Acquirer;
use Illuminate\Http\Request;

class AcquirerController extends Controller
{
    public function index()
    {
        $list = Acquirer::all();
        return view('acquirers.index', compact('list'));
    }

    public function create()
    {
        return view('acquirers.create');
    }

    public function store(Request $req)
    {
        $req->validate([
            'name' => 'required|unique:acquirers,name',
            'display_name' => 'required',
            'api_url' => 'required|url',
            'public_key' => 'required',
            'secret_key' => 'required',
        ]);

        Acquirer::create($req->all());

        return redirect()->route('acquirers.index')->with('ok', 'Adquirente adicionada!');
    }

    public function edit(Acquirer $acquirer)
    {
        return view('acquirers.edit', compact('acquirer'));
    }

    public function update(Request $req, Acquirer $acquirer)
    {
        $req->validate([
            'display_name' => 'required',
            'api_url' => 'required|url',
            'public_key' => 'required',
            'secret_key' => 'required',
        ]);

        $acquirer->update($req->all());

        return redirect()->route('acquirers.index')->with('ok', 'Atualizada com sucesso!');
    }

    public function destroy(Acquirer $acquirer)
    {
        $acquirer->delete();
        return redirect()->route('acquirers.index')->with('ok', 'Apagada!');
    }
}

<?php

namespace App\Http\Controllers\Activity;

use App\Http\Controllers\Controller;
use App\Models\GameType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GameTypeController extends Controller
{

    public function index()
    {
        $data['datas'] = GameType::orderBy('id', 'DESC')->get();
        
        return view('backend.activity.game-type.index', $data);
    }

    public function create()
    {
        return view('backend.activity.game-type.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'no_of_players' => 'required|integer|min:1',
        ]);

        $gameType = new GameType();
        $gameType->name = $request->name;
        $gameType->no_of_players = $request->no_of_players;
        $gameType->save();

        return redirect()->route('admin.game_types')->with('success', 'Game type created successfully.');
    }

    public function status($id)
    {
        $slot = GameType::findOrFail(decrypt($id));
        $slot->status = $slot->status == 'Active' ? 'Inactive' : 'Active';
        $slot->save();

        return redirect()->route('admin.game_types')->with('success', 'Game type status updated successfully.');
    }

    public function edit($id)
    {
        $data['data'] = GameType::findOrFail(decrypt($id));

        return view('backend.activity.game-type.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'no_of_players' => 'required|integer|min:1',
        ]);

        $gameType = GameType::findOrFail(decrypt($id));
        $gameType->name = $request->name;
        $gameType->no_of_players = $request->no_of_players;
        $gameType->save();

        return redirect()->route('admin.game_types')->with('success', 'Game type updated successfully.');
    }

    public function destroy($id)
    {
        $slot = GameType::findOrFail(decrypt($id));

        $slot->delete();

        return redirect()->route('admin.game_types')->with('success', 'Game type deleted successfully.');
    }
}
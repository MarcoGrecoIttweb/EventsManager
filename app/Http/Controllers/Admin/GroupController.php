<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index()
    {
        $groups = Group::withCount('members')->orderBy('nome')->get();
        return view('admin.groups.index', compact('groups'));
    }

    public function create()
    {
        return view('admin.groups.create');
    }

    public function store(Request $request)
    {
        $request->validate(['nome' => 'required|string|max:250|unique:gruppi,nome']);
        Group::create(['nome' => $request->nome]);
        return redirect()->route('admin.groups.index')->with('success', 'Gruppo creato con successo!');
    }

    public function show(Group $group)
    {
        $group->load('members');
        $nonMembers = User::where('ruolo', '!=', 0)
            ->whereNotIn('userID', $group->members->pluck('userID'))
            ->orderBy('username')
            ->get();
        return view('admin.groups.show', compact('group', 'nonMembers'));
    }

    public function edit(Group $group)
    {
        return view('admin.groups.edit', compact('group'));
    }

    public function update(Request $request, Group $group)
    {
        $request->validate(['nome' => 'required|string|max:250|unique:gruppi,nome,' . $group->getKey() . ',Id_gruppo']);
        $group->update(['nome' => $request->nome]);
        return redirect()->route('admin.groups.index')->with('success', 'Gruppo aggiornato!');
    }

    public function destroy(Group $group)
    {
        $group->members()->detach();
        $group->delete();
        return redirect()->route('admin.groups.index')->with('success', 'Gruppo eliminato!');
    }

    public function addMember(Request $request, Group $group)
    {
        $request->validate(['user_id' => 'required|exists:utente,userID']);
        $group->members()->syncWithoutDetaching([$request->user_id]);
        return back()->with('success', 'Membro aggiunto!');
    }

    public function removeMember(Group $group, User $user)
    {
        $group->members()->detach($user->getKey());
        return back()->with('success', 'Membro rimosso!');
    }
}

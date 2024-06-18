<?php

namespace App\Http\Controllers;

use App\Models\Count;
use App\Models\Habit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Habit $habit): View
    {
        return view('counts', ['habit' => $habit]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Count $count)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Count $count)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Count $count)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Count $count)
    {
        //
    }
}

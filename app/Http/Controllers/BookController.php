<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index()
    {
        return view('books.index');
    }

    public function show($book)
    {
        return view('books.show');
    }

    public function create()
    {
        return view('books.create');
    }

    public function edit($book)
    {
        return view('books.edit');
    }
}

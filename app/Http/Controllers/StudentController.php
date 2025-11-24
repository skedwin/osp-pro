<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function registration()
    {
        return view('student.registration', [
            'title' => 'Student Registration',
            'page' => 'student-registration'
        ]);
    }

    public function examination()
    {
        return view('student.examination', [
            'title' => 'Student Examination',
            'page' => 'student-examination'
        ]);
    }

    public function internship()
    {
        return view('student.internship', [
            'title' => 'Student Internship',
            'page' => 'student-internship'
        ]);
    }

    public function indexing()
    {
        return view('student.indexing', [
            'title' => 'Student Indexing',
            'page' => 'student-indexing'
        ]);
    }
}
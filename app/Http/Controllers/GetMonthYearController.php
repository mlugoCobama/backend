<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GetMonthYearController extends Controller
{
    public function getMonth() {
        return date('m');
    }

    public function getYear() {
        return date('Y');
    }

    public function getMonthPrev() {
        return date( 'm', strtotime(date_create('Y-m-d'). "-1 month"));
    }

    public function getYearPrev() {
        return date( 'Y', strtotime(date_create('Y-m-d'). "-1 year"));
    }
}

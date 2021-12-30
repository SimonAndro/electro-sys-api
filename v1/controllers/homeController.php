<?php

class HomeController extends Controller
{

    public function index()
    {
        return json_encode([
            "message"=>"success",
            "value"=>"electro sys version 1.0 api",            
        ]);
    }
}
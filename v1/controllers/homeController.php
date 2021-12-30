<?php

class HomeController extends Controller
{

    public function index()
    {
        return json_encode([
            "msg"=>"success",
            "value"=>"electro sys version 1.0 api",            
        ]);
    }
}
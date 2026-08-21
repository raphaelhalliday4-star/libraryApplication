<?php

namespace App\Http\Controllers;


use OpenApi\Attributes as OA;


#[OA\Info(
    version: "1.0.0",
    x: [
        "logo" => [
            "url" => "https://via.placeholder.com/190x90.png?text=L5-Swagger"
        ]
    ],
    title: "L5 OpenApi",
    description: "L5 Swagger OpenApi description",
    contact: new OA\Contact(
        email: "darius@matulionis.lt"
    )
)]

#[
    OA\SecurityScheme(
        securityScheme: "bearerAuth",
        type: "http",
        scheme: "bearer",
        bearerFormat: "JWT"
    )
]

class L5SwaggerAnnotationsExampleInfo
{
}
abstract class Controller
{
    //
}

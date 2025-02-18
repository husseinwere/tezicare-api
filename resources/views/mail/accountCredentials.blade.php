<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Account Credentials</title>

        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');

            #mail {
                font-size: 18px;
                font-family: 'Inter';
                max-width: 640px;
                width: 100%;
                margin: auto;
                padding: 20px;
                border: 1px solid #ccc;
            }

            .logo img {
                width: 120px;
            }

            p {
                margin-bottom: 32px;
            }

            button {
                background-color: #00a8ff;
                color: #fff;
                cursor: pointer;
                width: 200px;
                height: 40px;
                outline: none;
                border: none;
                font-weight: 600;
                font-size: 14px;
            }

            button:hover {
                background-color: #0097e6;
            }
        </style>
    </head>
    <body>
        <div id="mail">
            <div class="logo">
                <img src="{{ asset('images/logo.png') }}">
            </div>
            <p>
                Hi {{$name}},
            </p>
            <p>
                An account has been created for you at Tezicare for hospital management. Here are your credentials.
            </p>
            <p>
                Email: <strong>{{$email}}</strong><br>
                Password: <strong>{{$password}}</strong>
            </p>
            <p>Click the button below to login and access your account.</p>
            <a href="https://care-v2.tezi.co.ke" target="_blank"><button>GO TO PORTAL</button></a>
        </div>
    </body> 
</html>

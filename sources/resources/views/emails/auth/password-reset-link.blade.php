<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Reset Password</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { width: 90%; max-width: 600px; margin: 20px auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; }
        .header { background-color: #f8f8f8; padding: 20px; text-align: center; }
        .content { padding: 30px; }
        .content p { margin-bottom: 20px; }
        .button {
            display: inline-block;
            background-color: #3490dc; /* Warna biru */
            color: #ffffff;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .footer { background-color: #f8f8f8; padding: 20px; text-align: center; font-size: 12px; color: #888; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2>TSU Homebase</h2>
    </div>
    <div class="content">
        <p><strong>Halo!</strong></p>
        <p>Anda menerima email ini karena kami menerima permintaan reset password untuk akun Anda.</p>

        <p style="text-align: center;">
            <a href="{{ $resetUrl }}" class="button">Reset Password</a>
        </p>

        <p>Token reset password ini akan kedaluwarsa dalam 60 menit.</p>
        <p>Jika Anda tidak merasa melakukan permintaan ini, abaikan saja email ini.</p>
        <br>
        <p>
            Terima kasih,<br>
            Tim Siakad TSU Homebase
        </p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} TSU Homebase. All rights reserved.
    </div>
</div>
</body>
</html>

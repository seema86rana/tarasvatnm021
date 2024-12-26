<!DOCTYPE html>
<html>
<head>
    <title>Your Report</title>
</head>
<body>
    <table style="width: 100%; font-family: Arial, sans-serif; border-collapse: collapse;">
        <tr style="background-color: #f4f4f4; text-align: center;">
            <td style="padding: 20px;">
                <img src="{{ asset('/') }}assets/logo.svg" alt="{{ $mailData['companyName'] }}" style="max-height: 50px;">
            </td>
        </tr>
        <tr>
            <td style="padding: 20px; text-align: left;">
                <h2>Hello,</h2>
                <p>Your <strong>{{ $mailData['reportType'] }}</strong> report is ready!</p>
                <p><strong>Report Date:</strong> {{ $mailData['reportDate'] }}</p>
                <p>Click the button below to view your report:</p>
                <p style="text-align: center;">
                    <a href="{{ $mailData['reportLink'] }}" style="background-color: #007bff; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px;">View Report</a>
                </p>
                <p>Thank you for using {{ $mailData['companyName'] }}!</p>
            </td>
        </tr>
        <tr style="background-color: #f4f4f4; text-align: center;">
            <td style="padding: 10px; font-size: 12px; color: #666;">
                &copy; {{ date('Y') }} {{ $mailData['companyName'] }}. All rights reserved.
            </td>
        </tr>
    </table>
</body>
</html>

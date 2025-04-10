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

                <h2>Dear  {{ $mailData['userName'] }},</h2>
                <p>I hope you are doing well.</p>
                <p>Your <strong>{{ $mailData['reportType'] }}</strong> report is ready!</p>
                <br>
                <p>Attached is the {{ $mailData['subject'] }}. This report provides a detailed analysis comparing efficiency, speed, no of stoppage and pick for performance between {{ $mailData['previousDay'] }} and {{ $mailData['currentDay'] }}. The data highlights any significant changes and provides insights into sales, performance, market trends, etc.</p>
                <br>
                <p>Please feel free to reach out if you have any questions or need further clarification on any section of the report.</p>
                <p>Looking forward to hearing your thoughts or feedback.</p>
                <br>
                <p><b>Best regards,</b></p>
                <p>{{ $mailData['companyName'] }}</p>
                <p>[Your Position]</p>
                <p>[Your Contact Information]</p>
                <br>
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

<!doctype html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Quantum Capital Global</title>
    </head>
    <body>
        <p>Transaction ID : {{ $emailData['payment_id'] }}</p>
        <p>Type : {{ $emailData['type'] }}</p>
        <p>Amount : ${{ $emailData['amount'] }}</p>
        <p>Account No. : {{ $emailData['account_no'] }}</p>
    </body>
</html>
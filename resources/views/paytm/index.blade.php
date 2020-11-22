<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paytm Testing</title>
</head>

<body>
    <h1>Hello from paytm</h1>
    <form name="f1" id="form1" action="http://localhost:8000/api/paytm/request" method="POST">
        <input type="text" id="ORDER_ID" name="ORDER_ID" value="{{$order_id}}">
        <input type="text" id="INDUSTRY_TYPE_ID" name="INDUSTRY_TYPE_ID" value="Retail">
        <input type="text" id="CUST_ID" name="CUST_ID" value="dhavaldhaval123">
        <input type="text" id="CHANNEL_ID" name="CHANNEL_ID" value="WEB">
        <input type="text" id="TXN_AMOUNT" name="TXN_AMOUNT" value="{{$txn_amount}}">
        <input type="text" id="WEBSITE" name="WEBSITE" value="WEBSTAGING">
        <input type="text" id="MID" name="MID" value="bNFQOE57390015402549">
        <input type="text" id="PAYTM_MERCHANT_KEY" name="PAYTM_MERCHANT_KEY" value="cbrSYHCdxMR1gYeD">
        <button type="submit">SUBMIT</button>
    </form>
    <script>
        window.document.forms[0].submit();
    </script>
</body>

</html>
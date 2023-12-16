<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Email</title>
</head>
<body style="font-family:Arial,Helvetica,sans-serif;font-size:16px;">
@if($mailData['userType'] == 'customer')
    <h1>Thanks for your Order!!</h1>
    <h2>Your Order ID is:#{{ $mailData['order']->id }}</h2>
@else
<h1>You have received ad order :</h1>
<h2>Your Order ID is:#{{ $mailData['order']->id }}</h2>
@endif

    <h2 >Shipping Address</h2>
        <address>
            <strong>{{ $mailData['order']->first_name.' '.$mailData['order']->last_name }}</strong><br>
                    {{ $mailData['order']->address }}<br>
                    {{ $mailData['order']->city }}, {{ $mailData['order']->zip }} {{ getCountryInfo($mailData['order']->country_id)->name }}<br>
                    Phone: (+88) {{ $mailData['order']->mobile }}<br>
                    Email: {{ $mailData['order']->email }}
        </address>
    <h2>Products</h2>
    <table cellpadding="3" cellspacing="3" border="0" >
                                            <thead>
                                                <tr style="background:#ccc;">
                                                    <th>Product</th>
                                                    <th>Price</th>
                                                    <th >Qty</th>                                        
                                                    <th >Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($mailData['order']->items as $orderItem)                                             
                                                <tr>
                                                    <td>{{ $orderItem->name }}</td>
                                                    <td>{{ number_format($orderItem->price,2) }}Tk.</td>                                        
                                                    <td>{{ $orderItem->qty }}</td>
                                                    <td>{{ number_format($orderItem->total,2) }}Tk.</td>
                                                </tr>
                                                @endforeach
                                                <tr>
                                                    <th colspan="3" align="right">Subtotal:</th>
                                                    <td>{{ number_format($mailData['order']->subtotal,2) }}Tk.</td>
                                                </tr>
                                                
                                                <tr>
                                                <th colspan="3" align="right">Discount: {{ !empty($order->coupon_code) ? '(' . $mailData['order']->coupon_code . ')' : '' }}</th>
                                                    <td>{{ number_format($mailData['order']->discount,2) }}</td>
                                                </tr>
                                                
                                                <tr>
                                                    <th colspan="3" align="right">Shipping:</th>
                                                    <td>{{ number_format($mailData['order']->shipping,2) }}</td>
                                                </tr>
                                                <tr>
                                                <th colspan="3" align="right">Grand Total:</th>
                                                    <td>{{ number_format($mailData['order']->grand_total,2) }}Tk.</td>
                                            </tbody>
                                        </table>	
</body>
</html>
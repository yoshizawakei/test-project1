<!DOCTYPE html>
<html>
<head>
    <title>購入手続きが完了しました</title>
</head>

<body>
    <<p>
        {{ $item->user->name }}様
    </p>

    <p>
        ご出品いただいた商品「**{{ $item->item_name }}**」の購入手続きが完了しました。
    </p>

    <p>
        取引チャットにて購入者様とご連絡をお取りください。
    </p>

    <p>
        --- 取引情報 ---<br>
        商品名: {{ $item->item_name }}<br>
        価格: ¥{{ number_format($item->price) }}<br>
        購入者: {{ $item->buyer->name ?? 'N/A' }}<br>
        ----------------
    </p>

    <p>
        今後ともよろしくお願いいたします。
    </p>
</body>
</html>

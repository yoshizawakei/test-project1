<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>評価のお知らせ</title>
</head>

<body style="font-family: sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 20px auto; border: 1px solid #ddd; padding: 20px; border-radius: 8px;">

        <h2 style="color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 10px;">取引相手からの評価のお知らせ</h2>

        <p>{{ $ratedUser->name }} 様</p>

        <p>
            いつもご利用ありがとうございます。<br>
            取引が完了し、**{{ $raterUser->name }}** さんからの評価が届きました。
        </p>

        <div style="background-color: #f8f9fa; padding: 15px; border-radius: 4px; margin-top: 20px;">
            <p style="margin-top: 0;"><strong>評価対象の商品</strong></p>
            <p style="font-size: 1.1em; font-weight: bold; color: #212529;">{{ $transaction->item->item_name }}</p>
        </div>

        <p>
            マイページ（取引画面）から評価内容をご確認いただけます。<br>
            <a href="{{ route('mypage.index') }}"
                style="display: inline-block; margin-top: 15px; padding: 10px 20px; background-color: #28a745; color: #ffffff; text-decoration: none; border-radius: 5px;">
                マイページへ
            </a>
        </p>

        <hr style="margin-top: 30px; border: 0; border-top: 1px solid #eee;">
        <p style="font-size: 0.8em; color: #6c757d;">COACHTECHフリマ</p>
    </div>
</body>

</html>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yêu cầu nhập thông tin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .modal-box {
            background: #fff;
            border-radius: 15px;
            text-align: center;
            padding: 40px 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.8); }
            to { opacity: 1; transform: scale(1); }
        }
        .btn-confirm {
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 25px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .btn-confirm:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="modal-box">
        <h4>⚠️ Yêu cầu nhập đầy đủ thông tin</h4>
        <p>Để tiếp tục sử dụng hệ thống, vui lòng hoàn tất hồ sơ cá nhân của bạn.</p>
        <a href="{{ route('profile.edit') }}" class="btn-confirm">OK</a>
    </div>
</body>
</html>

<?php

namespace App\Http\Controllers;

use App\Models\ChatHistory;
use App\Models\Order;
use App\Models\Product;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenAI\Client;

class ChatBotController extends Controller
{
    public function send(Request $request)
    {
        $client = \OpenAI::client(env('OPENAI_API_KEY'));
        $userMessage = $request->message;

        $products = Product::take(5)->get(['name', 'quantity']);
        $orders = Order::take(5)->get(['id', 'status']);
        $vouchers = Voucher::take(3)->get(['code', 'discount', 'end_date']);

        $context = "Dưới đây là dữ liệu hiện có:\n";
        $context .= "Sản phẩm:\n";
        foreach ($products as $p) {
            $context .= "- {$p->name}: số lượng {$p->quantity}\n";
        }
        $context .= "\nĐơn hàng:\n";
        foreach ($orders as $o) {
            $context .= "- Đơn hàng #{$o->id}: {$o->status}\n";
        }
        $context .= "\nVoucher:\n";
        foreach ($vouchers as $v) {
            $context .= "- Mã {$v->code}, giảm {$v->discount}%, hạn {$v->end_date}\n";
        }

        $response = $client->chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => 'Bạn là chatbot hỗ trợ khách hàng cho website bán đồ cũ, trả lời bằng tiếng Việt, thân thiện và ngắn gọn.'],
                ['role' => 'system', 'content' => $context],
                ['role' => 'user', 'content' => $userMessage],
            ],
        ]);

        $reply = $response->choices[0]->message->content ?? "Xin lỗi, tôi chưa hiểu câu hỏi.";

        ChatHistory::create([
            'user_id'      => Auth::id(),
            'user_message' => $userMessage,
            'bot_reply'    => $reply,
        ]);

        return response()->json(['reply' => $reply]);
    }
}

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

namespace App\Http\Controllers\api\v1;
use App\Http\Controllers\Controller;

class WebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $webhookSecret = env('RAZORPAY_WEBHOOK_SECRET'); // Set this in .env

        $signature = $request->header('X-Razorpay-Signature');
        $payload = $request->getContent();

        // Verify Signature
        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        if (hash_equals($expectedSignature, $signature)) {
            $data = json_decode($payload, true);

            // Log received data
            Log::info('Razorpay Webhook received:', $data);

            // Example: Handle 'payment.captured' event
            if ($data['event'] === 'payment.captured') {
                $paymentId = $data['payload']['payment']['entity']['id'];
                $orderId = $data['payload']['payment']['entity']['order_id'];

                // Update your database
                DB::table('transactions')
                    ->where('razorpay_order_id', $orderId)
                    ->update([
                        'payment_status' => 'Paid',
                        'razorpay_payment_id' => $paymentId,
                        'transaction_date' => now(),
                    ]);

                Log::info('Transaction updated via webhook.', [
                    'razorpay_order_id' => $orderId,
                    'razorpay_payment_id' => $paymentId,
                ]);
            }

            return response()->json(['status' => 'success'], 200);
        } else {
            Log::warning('Invalid Razorpay webhook signature.', [
                'received_signature' => $signature,
                'expected_signature' => $expectedSignature,
            ]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }
    }
}

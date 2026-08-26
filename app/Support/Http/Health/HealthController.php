<?php

namespace App\Support\Http\Health;

use App\Support\Http\RequestId;
use Illuminate\Contracts\Mail\Factory;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Throwable;

class HealthController extends Controller
{
    public function live()
    {
        return response()->json(['status' => 'ok']);
    }

    public function ready()
    {
        $checks = [
            'app' => $this->checkApp(),
            'db' => $this->checkDatabase(),
            'queue' => $this->checkQueue(),
            'mail' => $this->checkMail(),
        ];

        $allOk = collect($checks)->every(fn ($check) => $check['status'] === 'ok');
        $status = $allOk ? 'ok' : 'fail';
        $httpStatus = $allOk ? 200 : 503;

        $data = [
            'status' => $status,
            'checks' => $checks,
        ];

        return response()->json(['data' => $data], $httpStatus)
            ->header('X-Request-Id', RequestId::current());
    }

    private function checkApp(): array
    {
        return ['status' => 'ok'];
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return ['status' => 'ok'];
        } catch (Throwable $e) {
            return [
                'status' => 'fail',
                'error' => substr($e->getMessage(), 0, 200),
            ];
        }
    }

    private function checkQueue(): array
    {
        try {
            $connection = Queue::connection();
            $connection->size();

            return ['status' => 'ok'];
        } catch (Throwable $e) {
            return [
                'status' => 'fail',
                'error' => substr($e->getMessage(), 0, 200),
            ];
        }
    }

    private function checkMail(): array
    {
        try {
            $transport = app(Factory::class)
                ->mailer()
                ->getSymfonyTransport();

            // Only call start/stop for transports that support it
            // (ArrayTransport and NullTransport used in tests don't need it)
            $transportClass = $transport::class;
            if (! str_contains($transportClass, 'ArrayTransport') && ! str_contains($transportClass, 'NullTransport')) {
                if (method_exists($transport, 'start')) {
                    $transport->start();
                }
                if (method_exists($transport, 'stop')) {
                    $transport->stop();
                }
            }

            return ['status' => 'ok'];
        } catch (Throwable $e) {
            return [
                'status' => 'fail',
                'error' => substr($e->getMessage(), 0, 200),
            ];
        }
    }
}

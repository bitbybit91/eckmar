<?php

namespace App\Console\Commands;

use App\AdminProfitTransfer;
use App\Marketplace\Utility\MoneroRPC\walletRPC;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ForwardXmrProfits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marketplace:forward-xmr-profits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Forward configured percentage of market XMR profits to admin wallet';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $walletAddress = trim((string) config('marketplace.admin_xmr_wallet'));
        $forwardPercent = max(0, min(100, intval(config('marketplace.admin_fee_forward_percent', 100))));
        $minBalance = intval(config('marketplace.admin_forward_min_balance', 1000000000000));

        if ($walletAddress === '') {
            Log::warning('XMR profit forwarding skipped: ADMIN_XMR_WALLET is not configured.');
            $this->warn('ADMIN_XMR_WALLET is not configured. Skipping.');
            return;
        }

        if ($forwardPercent <= 0) {
            Log::info('XMR profit forwarding skipped: ADMIN_FEE_FORWARD_PERCENT is 0.');
            $this->info('ADMIN_FEE_FORWARD_PERCENT is 0. Skipping.');
            return;
        }

        $moneroUser = env('MONERO_RPC_USER', env('MONERO_USERNAME', 'testwallet'));
        $moneroPass = env('MONERO_RPC_PASSWORD', env('MONERO_PASSWORD', 'testwallet'));

        try {
            $walletRpc = new walletRPC([
                'host' => env('MONERO_HOST', '127.0.0.1'),
                'port' => intval(env('MONERO_PORT', 28091)),
                'user' => $moneroUser,
                'password' => $moneroPass,
            ]);

            $balanceResponse = $walletRpc->get_balance();
            $unlockedBalance = isset($balanceResponse['unlocked_balance']) ? intval($balanceResponse['unlocked_balance']) : 0;

            if ($unlockedBalance <= 0) {
                Log::info('XMR profit forwarding skipped: wallet unlocked balance is empty.');
                $this->info('Unlocked balance is empty.');
                return;
            }

            $calculatedAmount = (int) floor(($unlockedBalance * $forwardPercent) / 100);
            $maxSendableAmount = max(0, $unlockedBalance - $minBalance);
            $forwardAmountAtomic = min($calculatedAmount, $maxSendableAmount);

            if ($forwardAmountAtomic < $minBalance) {
                Log::info('XMR profit forwarding skipped: amount below minimum threshold.', [
                    'forward_amount_atomic' => $forwardAmountAtomic,
                    'minimum_threshold' => $minBalance,
                ]);
                $this->info('Forward amount is below ADMIN_FORWARD_MIN_BALANCE threshold.');
                return;
            }

            $transfer = AdminProfitTransfer::create([
                'amount_piconero' => $forwardAmountAtomic,
                'tx_hash' => '',
                'wallet_address' => $walletAddress,
                'status' => 'pending',
                'error_message' => null,
            ]);

            try {
                $result = $walletRpc->transfer([
                    'address' => $walletAddress,
                    'amount' => $forwardAmountAtomic / 1000000000000,
                    'priority' => 1,
                ]);

                $txHash = isset($result['tx_hash']) ? $result['tx_hash'] : '';

                $transfer->tx_hash = $txHash;
                $transfer->status = 'completed';
                $transfer->error_message = null;
                $transfer->save();

                Log::info('XMR profit forwarding completed.', [
                    'tx_hash' => $txHash,
                    'amount_piconero' => $forwardAmountAtomic,
                    'wallet_address' => $walletAddress,
                ]);

                $this->info('XMR profit forwarding completed. TX: ' . $txHash);
            } catch (\Exception $exception) {
                $transfer->status = 'failed';
                $transfer->error_message = mb_substr($exception->getMessage(), 0, 65535);
                $transfer->save();

                Log::error('XMR profit forwarding transfer failed.', [
                    'message' => $exception->getMessage(),
                ]);

                $this->error('Transfer failed. Check logs for details.');
            }
        } catch (\Exception $exception) {
            Log::error('XMR profit forwarding failed before transfer.', [
                'message' => $exception->getMessage(),
            ]);

            $this->error('Unable to forward XMR profits: ' . $exception->getMessage());
        }
    }
}

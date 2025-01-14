<?php

namespace App\Http\Controllers;

use App\Http\Resources\CurrencyResource;
use App\Http\Resources\ExchangeRateResource;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\UserResource;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\Settings;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

use function Laravel\Prompts\alert;

class TransactionController extends Controller
{
    protected ExchangeRate $excahngerate;
    protected Transaction $transaction;

    public function __construct(ExchangeRate $excahngerate , Transaction $transaction)
    {
        $this->excahngerate = $excahngerate;
        $this->transaction =$transaction;
    }


    public function index(Request $request)
    {
        $user=User::all();
        $authToken = session('auth_token');
        return Inertia::render('Admin/Transactions/index', [
            'users'=>$user,
            'token'=>$authToken
        ]);
    }


    public function create()
    {
        $todaysexchangerate = $this->excahngerate->getliveexchangerate();
        $user_id = Auth::user()->id;
        $transactions = TransactionResource::collection(
            Transaction::where('user_id', $user_id)->get()
        );
        $currencies = CurrencyResource::collection((Currency::all()));
        $extrainfo = $this->transaction->getextrainfo();
        $excahngerates = ExchangeRateResource::collection(ExchangeRate::all());
        return Inertia::render('Admin/Transactions/Create', ['transactions' => $transactions, 'excahngerates' => $excahngerates, 'currencies' => $currencies, 'todaysexchangerate' => $todaysexchangerate,'extrainfo'=>$extrainfo]); // Correct spelling
    }

    public function store(Request $request)
    {
        $user_id = Auth::user()->id;
        $currency_id = $request->excurrency['id'];
        $type = $request->type;
        $amount_from = $request->amount_from;
        $basecurrency = Settings::first();
        $basecurrency_id = $basecurrency->currency_id;
        $exchange_rate = $request->exchange_rate;
        $unit = $request->unit;
        if ($type == 'buy') {
            if ($unit == 1) {
                $from_curency_id = $currency_id;
                $to_curency_id = $basecurrency_id;
                $amount_to = $amount_from * $exchange_rate;
                $this->transaction->storebuytransaction($user_id, $from_curency_id, $to_curency_id, $amount_from, $amount_to, $type, $exchange_rate, $unit);
            } else {
                $from_curency_id = $currency_id;
                $to_curency_id = $basecurrency_id;
                $amount_to = ($amount_from / $unit) * $exchange_rate;
                $this->transaction->storebuytransaction($user_id, $from_curency_id, $to_curency_id, $amount_from, $amount_to, $type, $exchange_rate, $unit);
            }
        } else {
            if($unit == 1){
                $from_curency_id = $basecurrency_id;
                $to_curency_id = $currency_id;
                $amount_to = $amount_from * $exchange_rate;
                $this->transaction->storeselltransaction($user_id, $from_curency_id, $to_curency_id, $amount_from, $amount_to, $type, $exchange_rate, $unit);
            
            }else{
                $from_curency_id = $basecurrency_id;
                $to_curency_id = $currency_id;
                $amount_to = ($amount_from / $unit) * $exchange_rate;
                $this->transaction->storeselltransaction($user_id, $from_curency_id, $to_curency_id, $amount_from, $amount_to, $type, $exchange_rate, $unit);
            
            }
           }
        return redirect()->route('transactions.index');
    }
    public function edit(Transaction $transaction)
    {
        //dd($transaction); // This will show the transaction object

        $transactions = TransactionResource::collection(Transaction::all());
        $currencies = CurrencyResource::collection(Currency::all());
        //dd($transaction);
        return Inertia::render('Admin/Transactions/Edit', [
            'transaction' => TransactionResource::make($transaction),
            'transactions' => $transactions,
            'currencies' => $currencies
        ]);
    }

    public function updatetransaction(Request $request)
    {

        $user_id = Auth::user()->id;
        $transaction_id = $request->transaction;
        $currency_id = $request->excurrency;
        $type = $request->type;
        $amount_from = $request->amount_from;
        $basecurrency = Settings::first();
        $basecurrency_id = $basecurrency->currency_id;
        $status = $request->status;
        //dd($user_id,$currency_id,$type,$amount_from,$status,$transaction_id);
        if ($type == 'buy') {
            $from_curency_id = $currency_id;
            $to_curency_id = $basecurrency_id;

            $exchangerate = ExchangeRate::where('from_currency_id', $from_curency_id)
                ->where('to_currency_id', $to_curency_id)
                ->pluck('rate')
                ->first();
            $amount_to = $amount_from * $exchangerate;
            $exchange_rate_id = ExchangeRate::where('from_currency_id', $from_curency_id)
                ->where('to_currency_id', $to_curency_id)
                ->pluck('id')
                ->first();
        } else {
            $from_curency_id = $basecurrency_id;
            $to_curency_id = $currency_id;
            $exchangerate = ExchangeRate::where('from_currency_id', $from_curency_id)
                ->where('to_currency_id', $to_curency_id)
                ->pluck('rate')
                ->first();
            $amount_to = $amount_from * $exchangerate;
            $exchange_rate_id = ExchangeRate::where('from_currency_id', $from_curency_id)
                ->where('to_currency_id', $to_curency_id)
                ->pluck('id')
                ->first();
        }

        $from_wallet_id = Wallet::where('currency_id', $from_curency_id)
            ->where('user_id', $user_id)
            ->pluck('id')
            ->first();
        $to_wallet_id = Wallet::where('currency_id', $to_curency_id)
            ->where('user_id', $user_id)
            ->pluck('id')
            ->first();

        $transaction = Transaction::findOrFail($transaction_id);

        // Update the transaction with the provided values
        $transaction->update([
            'user_id' => $user_id,
            'from_wallet_id' => $from_wallet_id,
            'to_wallet_id' => $to_wallet_id,
            'from_amount' => $amount_from,
            'to_amount' => $amount_to,
            'exchange_rate_id' => $exchange_rate_id,
            'status' => $status,
            'type' => $type,
        ]);
        return redirect()->route('transactions.index');
    }

    public function destroy($transaction_id)
    {
        $transaction = Transaction::findOrFail($transaction_id);
        $transaction->delete();
        return redirect()->route('transactions.index')->with('success', 'Transaction deleted successfully.');
    }
}
<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReservationRequest;
use App\Models\Customer;
use App\Models\Flight;
use App\Models\Reservation;
use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReservationsController extends Controller
{
      /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index($pagination = PAGINATION)
    {
        $reservations = Reservation::where('deleted_at', null)->paginate($pagination, ['*'], 'page', $_GET['page'] ?? 1);
        return view('reservations/show', compact('reservations', 'pagination'));
    }

    public function add()
    {
        $customers = Customer::where('deleted_at', null)->get();
        $flights = Flight::where('deleted_at', null)->get();
        return view('reservations/add', compact('flights', 'customers'));
    }
 
    public function store(ReservationRequest $request)
    {
        while (true) {
            $reservation_id = md5(rand());
            $reservation = Reservation::where('reservation_id', $reservation_id)->first();
            if (!$reservation) {
                break;
            }
        }
        Reservation::create([
            'reservation_id' => $reservation_id,
            'customer_id' => $request->customer_id,
            'flight_id' => $request->flight_id,
            'date' => Carbon::createFromFormat('Y-m-d', $request->date),
        ]);
        return redirect(route('reservation.showAll'))->with('success', 'Reservation Saved Successfully with ID number: ' . $reservation_id);
    }

    public function edit($reservationId)
    {
        $reservation = Reservation::where('deleted_at', null)->where('reservation_id', $reservationId)->first();
        if (!$reservation) {
            return redirect()->back()->with('error', 'Something Went Wrong');
        }
        $customers = Customer::where('deleted_at', null)->get();
        $flights = Flight::where('deleted_at', null)->get();
        return view('reservations/edit', compact('reservation', 'customers', 'flights'));
    }

    public function update(ReservationRequest $request)
    {
        $reservation = Reservation::where('deleted_at', null)->where('reservation_id', $request->reservation_id);
        if (!$reservation->first()) {
            return redirect()->back()->with('error', 'Something Went Wrong');
        }
        $reservation->update([
            'flight_id' => $request->flight_id ?? $reservation->first()->flight_id,
            'customer_id' => $request->customer_id ?? $reservation->first()->customer_id,
        ]);
        return redirect()->back()->with('success', 'Reservation Updated Successfully');
    }

    public function delete($reservationId)
    {
        $reservation = Reservation::where('reservation_id', $reservationId);
        if (!$reservation) {
            return redirect()->back()->with('error', 'Something Went Wrong');
        }
        $reservation->update(['deleted_at' => now()]);
        return redirect(route('reservation.showAll'))->with('success', 'Reservation Number: ' . $reservation->reservation_id . ' Deleted Successfully');
    }

    public function filter(Request $request)
    {
        $pagination = $request->pagination ?? PAGINATION;
        if (!$request->data) { // if There is no searching data return all hotels
            $reservations = Reservation::where('deleted_at', null)->paginate($pagination, ['*'], 'page', $request->page ?? 1);
            $data = '';
        } else {
            $data = $request->data;

            // Get reservations
            $reservations = Reservation::where('reservation_id', 'like', '%' . $request->data . '%')
            ->orWhere('customer_id', 'like', '%' . $request->data . '%')
            ->orWhere('flight_id', 'like', '%' . $request->data . '%')
            ->paginate($pagination, ['*'], 'page', $request->page ?? 1);
        }
        return view('reservations/show', compact('reservations', 'pagination', 'data'));
    }

    public function exportPdf($reservationId){
        $reservation = Reservation::where('reservation_id', $reservationId)->first();
        
        if(!$reservation) {
            return redirect()->back()->with('error', 'Some thing went wrong');
        }
        $customer = Customer::where('customer_id', $reservation->customer_id)->first();
        $flight = Flight::where('flight_id', $reservation->flight_id)->first();

        $view = view('reservations/pdf', [
            'reservation' => $reservation,
            'customer' => $customer,
            'flight' => $flight
        ])->render();

        $arabic = new Arabic();
        $p = $arabic->arIdentify($view);

        for ($i = count($p) - 1; $i >= 0; $i -= 2) {
            $utf8ar = $arabic->utf8Glyphs(substr($view, $p[$i - 1], $p[$i] - $p[$i - 1]));
            $view = substr_replace($view, $utf8ar, $p[$i - 1], $p[$i] - $p[$i - 1]);
        }

        $pdf = Pdf::loadHTML($view)->setPaper([0, 0, 800, 1100], 'portrait');
        return $pdf->download('Reservation_'. $reservation->reservation_id .'.pdf');
    }
}

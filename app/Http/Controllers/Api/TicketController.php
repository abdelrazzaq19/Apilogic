<?php

namespace App\Http\Controllers\Api;

use App\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    use ApiResponse;

    // Fungsi untuk User mendaftar ke Event
    public function store(Request $request, $eventId)
    {
        $user = $request->user();
        
        return DB::transaction(function () use ($user, $eventId) {
            $event = Event::where('id', $eventId)->lockForUpdate()->first();

            if (!$event) {
                return $this->errorResponse('Event tidak ditemukan', 404);
            }

            // Cek apakah user sudah punya tiket aktif untuk event ini
            $existingTicket = Ticket::where('user_id', $user->id)
                ->where('event_id', $eventId)
                ->where('is_canceled', false)
                ->exists();

            if ($existingTicket) {
                return $this->errorResponse('Anda sudah terdaftar di event ini', 400);
            }

            // Cek kuota reservasi
            $currentBookings = Ticket::where('event_id', $eventId)->where('is_canceled', false)->count();
            if ($currentBookings >= $event->max_reservation) {
                return $this->errorResponse('Maaf, kuota event sudah penuh', 400);
            }

            // Generate Kode Unik
            $payload = [
                'un' => $user->name,
                'en' => $event->name,
                'ts' => now()->timestamp
            ];
            $encode = base64_encode(json_encode($payload));
            $code = 'ikutan-' . strtoupper(uniqid()) . '-' . $encode;

            $ticket = Ticket::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'code' => $code,
                'is_canceled' => false
            ]);

            return $this->successResponse($ticket->load('event'), 'Berhasil mendaftar event!', 201);
        });
    }

    // List tiket milik user yang sedang login
    public function indexByUser(Request $request)
    {
        $tickets = Ticket::where('user_id', $request->user()->id)
            ->with('event')
            ->latest()
            ->get();

        return $this->successResponse($tickets, 'Tickets fetched successfully');
    }
}
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
    public function store(Request $request, $eventId)
    {
        $event = Event::find($eventId);
        if (!$event) {
            return $this->errorResponse('Event not found', 404);
        }

        $user = $request->user();

        DB::beginTransaction();
        try {
            $event = Event::where('id', $eventId)->lockForUpdate()->firstOrFail();

            $existingTicket = Ticket::where('user_id', $user->id)->where('event_id', $event->id)->where('is_canceled, false')->exist();

            if ($existingTicket) {
                DB::rollBack();
                return $this->errorResponse('User already has a ticket for this event', 400);
            }

            $currentBookings = $event->tickets()->where('is_canceled', false)->count();

            if ($currentBookings >= $event->max_reservation) {
                DB::rollBack();
                return $this->errorResponse('Event is fully booked', 400);
            }

            $payload = [
                'un' => $user->name,
                'ue' => $user->email,
                'en' => $event->name,
                'ed' => $event->date,
            ];

            $encode = base64_encode(json_encode($payload));

            $code = 'ikutan-' . uniqid() . '-' . $encode;

            $ticket = Ticket::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'code' => $code,
            ]);

            DB::commit();
            return $this->successResponse($ticket, 'Ticket created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 500);
        }
    } 
} // video ke 6 16:14

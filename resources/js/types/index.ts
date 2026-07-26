// resources/js/types/index.ts
export type PassengerType = 'ADULT' | 'INFANT'
export type TrainClass = 'EXECUTIVE' | 'BUSINESS' | 'ECONOMY'
export type BookingStatus = 'CONFIRMED' | 'CANCELLED' | 'COMPLETED'
export type UserRole = 'ADMIN' | 'OFFICER' | 'PASSENGER'
export type ActionType = 'GATE_IN' | 'GATE_OUT' | 'BOARDING'

export interface Passenger {
  passenger_id: number
  bookings?: Booking[]
  identity_number: string
  full_name: string
  email: string | null
  phone_number: string | null
  passenger_type: PassengerType
  detail?: {
    address: string | null
    emergency_contact_name: string | null
    emergency_contact_phone: string | null
  } | null
}

export interface Train {
  train_id: number
  train_code: string
  train_name: string
  train_class: TrainClass
  total_seats: number
}

export interface Schedule {
  schedule_id: number
  train_id: number
  origin_station: string
  destination_station: string
  departure_time: string
  arrival_time: string
  base_ticket_price: number
  train?: Train
}

export interface Booking {
  booking_id: number
  passenger_id: number
  schedule_id: number
  booking_code: string
  seat_number: string
  booking_status: BookingStatus
  total_paid: number
  schedule?: Schedule
  passenger?: Passenger
}

export interface AuthUser {
  id: number
  name: string
  email: string
  role: UserRole
  passenger_id: number | null
}

export interface PageProps {
  auth: { user: AuthUser | null }
  flash?: { success?: string; error?: string; final_price?: number }
  [key: string]: unknown
}
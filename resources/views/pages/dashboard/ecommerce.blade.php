@extends('layouts.app')

@section('content')
  <div class="grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12 space-y-6 xl:col-span-7">
      <x-ecommerce.ecommerce-metrics :metrics="$metrics" />
      <x-ecommerce.monthly-sale :chartData="$chartData" :chartLabel="$chartLabel" />
    </div>
    <div class="col-span-12 xl:col-span-5">
        <x-ecommerce.monthly-target :topEquipment="$topEquipment" />
    </div>

    <div class="col-span-12">
      <x-ecommerce.statistics-chart :equipmentByCategory="$equipmentByCategory" :equipmentByStatus="$equipmentByStatus" :reservationsByStatus="$reservationsByStatus" :rentalsByStatus="$rentalsByStatus" />
    </div>

    <div class="col-span-12 xl:col-span-5">
      <x-ecommerce.customer-demographic :customersByRole="$customersByRole" :totalUsers="$totalUsers" />
    </div>

    <div class="col-span-12 xl:col-span-7">
      <x-ecommerce.recent-orders :recentOrders="$recentOrders" />
    </div>
  </div>
@endsection

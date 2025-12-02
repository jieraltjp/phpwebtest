<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * 显示仪表板主页
     */
    public function index()
    {
        return view('dashboard');
    }

    /**
     * 获取仪表板统计数据
     */
    public function getStats()
    {
        // 这里应该从数据库获取真实的订单统计数据
        // 目前返回模拟数据
        return response()->json([
            'tempStorage' => 0,
            'allOrders' => 1,
            'quoting' => 1,
            'awaitingPayment' => 0,
            'purchasing' => 0,
            'purchaseCompleted' => 0,
            'shipmentCompleted' => 0,
            'problemProducts' => 0,
            'awaitingPurchase' => 0,
            'inDelivery' => 0,
            'awaitingArrival' => 0,
            'arrivalPrep' => 0,
        ]);
    }
}
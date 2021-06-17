<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use GuzzleHttp\Client;
use Log;
use Telegram\Bot\Keyboard\Keyboard;
use App\Models\Food;
use App\Models\Data;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TelegramController extends Controller
{
    public function setWebhook()
    {
        $client = new Client();

        $token = Telegram::getAccessToken();
        $ng_domain = env('NG_DOMAIN');    
        $result = $client->get('https://api.telegram.org/bot'.$token.'/setWebhook?url=https://b932b6fa4e6b.ngrok.io/action');

        return (string) $result->getBody();
    }


    public function index(Request $request)
    {
        Log::info($request->all());
        // $client = Order::all();
        $user = $this->createIfNotExists($request->all());
        // $form = isset($data['message']) ? $data['message'] : $data['my_chat_member'] ;

        $firstname = isset($request['message']['chat']['first_name'])? $request['message']['chat']['first_name']:$request['my_chat_member']['chat']['first_name'];
        $chatId = isset($request['message']['chat']['id'])? $request['message']['chat']['id']:$request['my_chat_member']['chat']['id'];
        $username =isset($request['message']['chat']['username'])? $request['message']['chat']['username']:$request['my_chat_member']['chat']['username']??null;
        $text = $request->message['text']??null;

        
        if($username == 'Y0rq1n'){
            switch ($user->step){
                case 0: {
                    $this->admin_main($request, $chatId,$firstname,$user);
                }break;
                case 1: {
                    if($text=='Buyurtmalar'){
                    $this->admin_orders($request, $chatId,$firstname,$user);
                    }if($text=='Ovqatlar'){
                    $this->admin_foods($request, $chatId,$firstname,$user);
                    }if($text=='Ortga'){
                    $this->admin_main($request, $chatId,$firstname,$user);
                    }
                }break;
                case 2: {
                    if($text=='◀️Ortga'){
                    $this->admin_main($request, $chatId,$firstname,$user);
                    }if($text == '➕Qo\'shish'){
                    $this->admin_food_add($request, $chatId,$user);
                    }if($text == '❌O\'chirish'){
                    $this->admin_food_delete($request, $chatId,$firstname,$user);
                    }if($text == 'Tayyorlash'){
                    $this->order_complate($request, $chatId,$firstname,$user);
                    }
                }break;
                case 3: {
                    if($text != '◀️Ortga'){
                    $this->food_add($request, $chatId, $user);
                    }else{
                    $this->admin_main($request, $chatId,$firstname,$user);
                    }
                }break;
                case 4: {
                    $this->admin_main($request, $chatId,$firstname,$user);
                }break;
                case 5: {
                    if($text != '◀️Ortga'){
                    $this->food_delete($request, $chatId, $user);
                    }else{
                    $this->admin_foods($request, $chatId,$firstname,$user);
                    }
                }break;
                case 6: {
                    if($text != '◀️Ortga'){
                    $this->get_complate($request, $chatId, $user);
                    }else{
                    $this->admin_main($request, $chatId,$firstname,$user);
                    }
                }break;
                case 7: {
                    if($text == '◀️Ortga'){
                    $this->admin_orders($request, $chatId,$firstname,$user);
                    }
                }break;
            }    
            
            
        }else{
            switch($user->step){
                case 0: {
                    $this->clientStart($request, $chatId,$firstname,$user);
                }break;
                case 1: {
                    if($text == 'Buyurtma qilish'){
                        $this->clientMenu($request, $chatId,$firstname,$user);
                    }if($text == 'Buyurtma navbatini ko\'rish'){
                        $this->clientQueue($request, $chatId,$firstname,$user);
                    }break;
                }
                case 2: {
                    if($text == '◀️Ortga qaytish'){
                        $this->clientStart($request, $chatId,$firstname,$user);
                    }else{
                        $this->clientPhone($request, $chatId,$firstname,$user);
                    }
                }break;
                case 3: {
                    if($text == '◀️Ortga qaytish'){
                        $this->clientStart($request, $chatId,$firstname,$user);
                    }else{
                        $this->clientRegion($request, $chatId,$firstname,$user);
                    }
                }break;
                case 4: {
                    if($text == '◀️Ortga qaytish'){
                        $this->clientStart($request, $chatId,$firstname,$user);
                    }else{
                        $this->clientCity($request, $chatId,$firstname,$user);
                    }
                }break;
                case 5: {
                    if($text == '◀️Ortga qaytish'){
                        $this->clientStart($request, $chatId,$firstname,$user);
                    }else{
                        $this->clientAddress($request, $chatId,$firstname,$user);
                    }
                }break;
                case 6: {
                    if($text == '◀️Ortga qaytish'){
                        $this->clientStart($request, $chatId,$firstname,$user);
                    }else{
                        $this->clientFood($request, $chatId,$firstname,$user);
                    }
                }break;
                
            }
        }
        
        
    }
    public function clientStart($request, $chatId,$firstname,$user)
    {
        $keyboard__client = [
                    ['Buyurtma qilish'],
                    ['Buyurtma navbatini ko\'rish'],
        ];
        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard__client,
            'resize_keyboard' => true, 
            'one_time_keyboard' => true
        ]);

        Telegram::sendMessage([
            'chat_id' => $chatId, 
            'text' => 'Salom '.$firstname.'. Bizning botimizga xush kelibsiz',
            'reply_markup' => $reply_markup
        ]);
        $this->setUserStep($user, 1);
        
    }
    public function clientQueue($request, $chatId,$firstname,$user)
    {
        if(Order::all() !== null){
            $user_id = User::where('chat_id', $chatId)->first()->id;
            $order = Order::where('user_id', $user_id)->first()->id;
            $queue = Order::where('status', 0)->where('id', "<", $order)->count();
        }else{
            $queue = 0;
        }
        $keyboard__client = [
                    ['◀️Ortga qaytish'],
        ];
        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard__client,
            'resize_keyboard' => true, 
            'one_time_keyboard' => true
        ]);
        
        if($queue == 0 ){
            $queue = "Sizda hech qanday navbat mavjud emas";
        }

            Telegram::sendMessage([
                'chat_id' => $chatId, 
                'text' => $queue,
                'reply_markup' => $reply_markup
                ]);
        $this->setUserStep($user, 2);
    }

    public function clientMenu($request, $chatId,$firstname,$user)
    {
        $keyboard__client = [
            ['◀️Ortga qaytish'],
         ];           
        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard__client,
            'resize_keyboard' => true, 
            'one_time_keyboard' => true
        ]);
        $data = DB::table('foods')->get();
        Telegram::sendMessage([
            'chat_id' => $chatId, 
            'text' => "Telefon raqamizni kiriting👇",
            'reply_markup' => $reply_markup
        ]);
        $this->setUserStep($user, 2);
    }
    public function clientPhone($request, $chatId,$firstname,$user)
    {
        $keyboard__client = [
            ['◀️Ortga qaytish'],
        ]; 
        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard__client,
            'resize_keyboard' => true, 
            'one_time_keyboard' => true
        ]);
        $phone = $request['message']['text'];
        Data::create([
            'chat_id'=> $chatId,
            'phone'=> $phone
        ]);
        Telegram::sendMessage([
            'chat_id' => $chatId, 
            'text' => 'Viloyatinggizni kiriting',
            'reply_markup' => $reply_markup
        ]);
        $this->setUserStep($user, 3);

        // $this->clientRegion($request, $chatId,$phone,$user);
    }
    public function clientRegion($request, $chatId,$firstname,$user)
    {
        $keyboard__client = [
            ['◀️Ortga qaytish'],
        ]; 
        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard__client,
            'resize_keyboard' => true, 
            'one_time_keyboard' => true
        ]);
        $region = $request['message']['text'];
        Data::where('chat_id', $request['message']['chat']['id'])->update([
            'region' => $region,
        ]);
        Telegram::sendMessage([
            'chat_id' => $chatId, 
            'text' => 'Tumaninggizni kiriting',
            'reply_markup' => $reply_markup
        ]);
        $this->setUserStep($user, 4);
        // $this->clientCity($request, $chatId,$phone,$region,$user);
    }
    public function clientCity($request, $chatId,$firstname,$user)
    {
       $keyboard__client = [
            ['◀️Ortga qaytish'],
        ]; 
        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard__client,
            'resize_keyboard' => true, 
            'one_time_keyboard' => true
        ]);
        $city = $request['message']['text'];
        Data::where('chat_id', $request['message']['chat']['id'])->first()->update([
            'city' => $city,
        ]);
        Telegram::sendMessage([
            'chat_id' => $chatId, 
            'text' => 'Manzilinggizni kiriting',
            'reply_markup' => $reply_markup
        ]);
        $this->setUserStep($user, 5);
        // $this->clientAddress($request, $chatId,$phone,$region,$city,$user);
    }
    public function clientAddress($request, $chatId,$firstname,$user)
    {
        $keyboard__client = [
            ['◀️Ortga qaytish'],
        ]; 
        foreach(Food::all() as $food){
            array_push($keyboard__client,[$food->foods_name]);
        }
        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard__client,
            'resize_keyboard' => true, 
            'one_time_keyboard' => true
        ]);
        $address = $request['message']['text'];
        Data::where('chat_id', $request['message']['chat']['id'])->first()->update([
            'address' => $address,
        ]);
        Telegram::sendMessage([
            'chat_id' => $chatId, 
            'text' => 'Buyurtma bermoqchi bo\'lgan ovqatizni tanlang',
            'reply_markup' => $reply_markup
        ]);
        $this->setUserStep($user, 6);
        // $this->clientFood($request, $chatId,$phone,$region,$city,$address,$user);
    }
    public function clientFood($request, $chatId,$firstname,$user)
    {
        $keyboard__client = [
            ['Buyurtma qilish'],
            ['Buyurtma navbatini ko\'rish'],
        ]; 
        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard__client,
            'resize_keyboard' => true, 
            'one_time_keyboard' => true
        ]);
        $food = Food::where('foods_name', $request['message']['text'])->first()->id;
        
        $client = User::where('name',  $request['message']['chat']['first_name'])->first()->id;
        Order::create([
            'food_id' => $food,
            'user_id' => $client,
            'phone' => Data::where('chat_id', $request['message']['chat']['id'])->first()->phone,
            'region' => Data::where('chat_id', $request['message']['chat']['id'])->first()->region,
            'city' => Data::where('chat_id', $request['message']['chat']['id'])->first()->city,
            'address' => Data::where('chat_id', $request['message']['chat']['id'])->first()->address,
            
        ]);
        Telegram::sendMessage([
            'chat_id' => $chatId, 
            'text' => 'So\'rov yuborildi',
            'reply_markup' => $reply_markup
        ]);
        $this->setUserStep($user, 1);
    }



    public function setClientStatus($client, $status)
    {
        return Order::where('id', $client)->update([
            'status' => $status
        ]);
    }
    public function setUserStep($user, $step) {

       return $user->update([
            'step' => $step ?? $user->step + 1
        ]);
    }
    public function createIfNotExists($data) {
        $form = isset($data['message']) ? $data['message'] : $data['my_chat_member'] ;
        $user = User::where('chat_id', $form['chat']['id'])->first();

        if(!$user) {
            $user = User::create([
                'chat_id' => $form['chat']['id'],
                'name' => $form['chat']['first_name'],
                // 'last_name' => isset($form['chat']['last_name']) ? $form['chat']['last_name'] : null,
                // 'language_code' => isset($form['from']['language_code']) ? $form['from']['language_code'] : 'no',
                // 'username' => isset($form['chat']['username']) ? $form['chat']['username'] : null
            ]);
        } else {
            $user->update([
                'chat_id' => $form['chat']['id'],
                'name' => $form['chat']['first_name'],
            ]);
        }// karochchi $user degani bor osha faqat ismni chiqaropdi 

        return $user;
    }


    public function admin_main($request, $chatId,$firstname,$user)
    {
        // $chatId = $request->message['chat']['id'];

        $keyboard__admin = [
                    ['Buyurtmalar'],
                    ['Ovqatlar'],
        ];
        $keyboard_admin = Keyboard::make([
            'keyboard' => $keyboard__admin,
            'resize_keyboard' => true, 
            'one_time_keyboard' => true
        ]);

        Telegram::sendMessage([
            'chat_id' => $chatId, 
            'text' => 'Salom '.$firstname.'. siz adminsiz',
            'reply_markup' => $keyboard_admin
        ]);
        $this->setUserStep($user, 1);
    }

    public function admin_orders($request, $chatId,$firstname,$user)
    {
        $orders = Order::where('status', 0)->get();
        $count = Order::count();
        $keyboard__admin = [
                    ['Tayyorlash'],
                    ['◀️Ortga'],
        ];
        $keyboard_admin = Keyboard::make([
            'keyboard' => $keyboard__admin,
            'resize_keyboard' => true, 
            'one_time_keyboard' => true
        ]);
        $message = $count."ta buyurtma mavjud\n";
        foreach($orders as $order){
            $message  .= $order->id."-- name: ".$order->user->name."  ->  ".$order->food->foods_name."\n";
            }
            
            Telegram::sendMessage([
                'chat_id' => $chatId, 
                'text' => $message,
                'reply_markup' => $keyboard_admin
                ]);
        $this->setUserStep($user, 2);
    }
    public function order_complate($request, $chatId,$firstname,$user)
    {
        $keyboard__admin = [
                    ['◀️Ortga'],
        ];
        foreach(Order::all() as $order){
            array_push($keyboard__admin,["$order->id"]);
            // $msg = $order->id;
        }
        $keyboard_admin = Keyboard::make([
            'keyboard' => $keyboard__admin,
            'resize_keyboard' => true, 
            'one_time_keyboard' => true
        ]);
            Telegram::sendMessage([
                'chat_id' => $chatId, 
                'text' => 'Mijoz IDsini tanlang',
                'reply_markup' => $keyboard_admin
                ]);
        $this->setUserStep($user, 6);
    }
    public function get_complate($request, $chatId,$user)
    {
        $id_test = Order::where('id', $request['message']['text'])->first()->user_id;
        $client_chatId= User::where('id', $id_test)->first()->chat_id;
        $keyboard__admin = [
                    ['◀️Ortga'],
        ];
        $client = $request['message']['text'];
        $keyboard_admin = Keyboard::make([
            'keyboard' => $keyboard__admin,
            'resize_keyboard' => true, 
            'one_time_keyboard' => true
        ]);

        Telegram::sendMessage([
            'chat_id' => $chatId, 
            'text' => "Bajarildi",
            'reply_markup' => $keyboard_admin
        ]);
        Telegram::sendMessage([
            'chat_id' => $client_chatId, 
            'text' => "Sizning buyurtmanggiz tayyor bo'ldi, buyurtmanggiz tez orada yetkaziladi",
            // 'reply_markup' => $keyboard_admin
        ]);
        $this->setUserStep($user, 7);
        $this->setClientStatus($client, 1);

    }
    public function send_message($request, $chatId,$firstname,$user)
    {
        $keyboard__admin = [
                    ['◀️Ortga'],
        ];

        $keyboard_admin = Keyboard::make([
            'keyboard' => $keyboard__admin,
            'resize_keyboard' => true, 
            'one_time_keyboard' => true
        ]);

        Telegram::sendMessage([
            'chat_id' => $chatId, 
            'text' => "Yuborildi",
            'reply_markup' => $keyboard_admin
        ]);
       
        $this->setUserStep($user, 7);

    }
    public function admin_foods($request, $chatId,$firstname,$user)
    {
        $foods = Food::all();
        $keyboard__admin = [
                    ['➕Qo\'shish','❌O\'chirish'],
                    ['◀️Ortga']
        ];

        $keyboard_admin = Keyboard::make([
            'keyboard' => $keyboard__admin,
            'resize_keyboard' => true, 
            'one_time_keyboard' => true
        ]);
        $message = "Ovqatlar: \n";
        foreach($foods as $food){
            $message .= '-'.$food->foods_name."\n";
        }
        Telegram::sendMessage([
            'chat_id' => $chatId, 
            'text' => $message,
            'reply_markup' => $keyboard_admin
        ]);
        $this->setUserStep($user, 2);
    }
    public function admin_food_add($request,$chatId,$user)
    {
        $keyboard__admin = [
                    ['◀️Ortga']
        ];

        $keyboard_admin = Keyboard::make([
            'keyboard' => $keyboard__admin,
            'resize_keyboard' => true, 
            'one_time_keyboard' => true
        ]);

        Telegram::sendMessage([
            'chat_id' => $chatId, 
            'text' => 'Qo\'shmoqchi bo\'lgan ovqatinggizni kiriting',
            'reply_markup' => $keyboard_admin              
        ]);
        $this->setUserStep($user, 3);
    }
    public function food_add($request, $chatId, $user)
    {
        $text = $request['message']['text'];
      
        $food = Food::create([
            'foods_name' => $text, 
            
        ]);

        Telegram::sendMessage([
            'chat_id' => $chatId, 
            'text' => 'Muvofaqqiyatli kiritildi',
                        
        ]);
    }
    public function admin_food_delete($request, $chatId,$firstname,$user)
    {
        $keyboard__admin = [
                    ['◀️Ortga']
        ];

        
        foreach(Food::all() as $food){
            array_push($keyboard__admin,[$food->foods_name]);
        }
        $keyboard_admin = Keyboard::make([
            'keyboard' => $keyboard__admin,
            'resize_keyboard' => true, 
            'one_time_keyboard' => true
        ]);
        // $foods = Food::all();
       
        // foreach($foods as $food){
        Telegram::sendMessage([
            'chat_id' => $chatId, 
            'text' => "O'chirmoqchi bo'lgan ovqatinggizni korsating ",
            'reply_markup' => $keyboard_admin              
        ]);
        // }
        $this->setUserStep($user, 5);
    }
    public function food_delete($request, $chatId, $user)
    {
        
        $keyboard__admin = [
                    ['◀️Ortga']
        ];
        $text = $request['message']['text'];
      
        Food::where('foods_name' , $text)->delete();

        foreach(Food::all() as $food){
            array_push($keyboard__admin,[$food->foods_name]);
        }
        $keyboard_admin = Keyboard::make([
            'keyboard' => $keyboard__admin,
            'resize_keyboard' => true, 
            'one_time_keyboard' => true
        ]); 

        Telegram::sendMessage([
            'chat_id' => $chatId, 
            'text' => 'Muvofaqqiyatli o\'chirildi',
            'reply_markup' => $keyboard_admin              
        ]);
        $this->setUserStep($user, 5);

    }

}

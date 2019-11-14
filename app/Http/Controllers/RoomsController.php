<?php

namespace App\Http\Controllers;

use App\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use WebSocket\Client;


class RoomsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $list = Room::with('images')->get();
        $status = '200';
        $data = compact('list', 'status');

        return response()->json($data);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'price'=> 'required',
            //'images' => 'required',
            // 'images.*' => 'mimes:png,gif,jpeg',
        ], [
            'required' => 'Обязательное поле',
        ]);
        if($validator->passes()) {
            //сохранять в бд
            $room = Room::create([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'price' => $request->input('price')
            ]);
            $status = '201';
            $list = $room->id;
        } else {
            $status = '422';
            $list = $validator->errors();
        }
        $data = compact('list', 'status');

        $message = json_encode((string)$room);
        $client = new Client('ws://iphotelwebsocket.herokuapp.com/');
        $client->send($message);

        return response()->json($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $room = Room::with('images')->find($id);
        $list = $room;
        $status = $room ? '200' : '404';
        $data = compact('list','status');
        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $room = Room::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'price'=> 'required',
        ], [
            'required' => 'Обязательное поле',
        ]);
        if($validator->passes()) {
            //сохранять в бд
            $room->update([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'price' => $request->input('price')
            ]);
            $status = '201';
        }
        else {
                $status = '422';
                $room = $validator->errors();
            }
        $data = compact('room','status');
        return response()->json($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $room = Room::findOrFail($id);
        $images = $room->images()->pluck('original');
        foreach ($images as $image){
            Storage::disk('dropbox')->delete(substr($image, 1));
            Storage::disk('dropbox')->deleteDirectory(explode('/',trim(substr($image, 1)))[0]);
        }
        $room->images()->delete();
        $room->delete($id);
        $status = '204';

        $message = json_encode((string)$id);
        $client = new Client('ws://iphotelwebsocket.herokuapp.com/');
        $client->send($message);

        return response()->json($status);
    }

    public function search(Request $request){
        try {
            $text = mb_strtolower($request->input('text'), 'UTF-8');
            $text = "'$text'";
            $query = "CALL search(" . $text . ");";
            $rooms = DB::select($query);

            $status = '200';
            $list = $rooms;
        }
        catch(Exception $e){
            $status = '422';
            $list = $e->getMessage();
        }

        $data = compact('list', 'status');
        return response()->json($data);
    }

    public function searchby(Request $request)
    {
        try {
            $text = mb_strtolower($request->input('text'), 'UTF-8');
            $text = "'$text'";
            $query = "SELECT * FROM rooms WHERE title LIKE CONCAT('%',".$text.",'%') OR description LIKE CONCAT('%',".$text.",'%');";
            $rooms = DB::select($query);

            $status = '200';
            $list = $rooms;
        } catch (Exception $e) {
            $status = '422';
            $list = $e->getMessage();
        }

        $data = compact('list', 'status');
        return response()->json($data);
    }

    public function vkcallback(Request $request){
        if (($request->input('type') == 'confirmation' ) && ($request->input('type') == 'group_id' )){
            return response()-('aed9e1ac');
        }
    }
}

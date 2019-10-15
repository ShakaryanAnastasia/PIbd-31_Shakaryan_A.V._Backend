<?php

namespace App\Http\Controllers;

use App\Room;
use Illuminate\Http\Request;

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
            'description' => 'required',
            'images' => 'required',
            // 'images.*' => 'mimes:png,gif,jpeg',
        ], [
            'required' => 'Обязательное поле',
        ]);
        if($validator->passes()) {
            //сохранять в бд
            $room = Room::create([
                'description' => $request->input('description')
            ]);
            $images = $request->input('images');
            $room->images()->createMany([
                [
                    'original' => $images[0],
                ],
                [
                    'original' => $images[1],
                ]
            ]);
            $status = '201';
            $list = $room->id;
        } else {
            $status = '422';
            $list = $validator->errors();
        }
        $data = compact('list', 'status');
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
        $room->update($request->all());
        $status = '200';
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
        $room->images()->delete();
        $room->delete($id);
        $status = '204';
        return response()->json($status);
    }
}

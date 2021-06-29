<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//使うクラスの宣言
use App\Book; //Bookモデルをつかえるように
use Validator; //バリデーションがつかえるように
use Auth; //認証モデルがつかえるように

class BooksController extends Controller
{
    //コンストラクタ（このクラスが最初に呼ばれたら処理）
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    //本ダッシュボード表示
    public function index(){
        $books = Book::where('user_id',Auth::user()->id)
        ->orderBy('created_at', 'asc')->paginate(3);
        return view('books',[
            'books' => $books
        ]);
    }
    //更新画面
    public function edit ($book_id){
    $books = Book::where('user_id',Auth::user()->id)->find($book_id);
    //{books}id値を取得=>Book $book id値の1レコードを取得
    return view('booksedit', ['book'=>$books]);
    }
    
    //更新
    public function update(Request $request){
        //バリデーション
        $validator = Validator::make($request->all(), [
            'id'=>'required',
            'item_name'=>'required|min:3|max:255',
            'item_number'=>'required|min:1|max:3',
            'item_amount'=>'required|max:6',
            'published'=>'required',
            ]);
        
        //バリデーションエラー
        if($validator->fails()){
            return redirect('/')
            ->withInput()
            ->withErrors($validator);
        }
        
        //データ更新
        $books = Book::where('user_id', Auth::user()->id)->find($request->id);
        $books -> item_name = $request->item_name;
        $books -> item_number = $request->item_number;
        $books -> item_amount = $request->item_amount;
        $books -> published = $request->published;
        $books -> save();
        return redirect('/');
    }
    
    //登録
    public function store(Request $request){
            
        //バリデーション
        $validator = Validator::make($request->all(), [
            'item_name'=>'required|min:3|max:255',
            'item_number'=>'required|min:1|max:3',
            'item_amount'=>'required|max:6',
            'published'=>'required',
            ]);
        
        //バリデーションエラー
        if($validator->fails()){
            return redirect('/')
            ->withInput()
            ->withErrors($validator);
        }
        
        //ファイルアップロード処理
        $file = $request->file('item_img'); //ファイル取得
        if(!empty($file)){
            $filename = $file->getClientOriginalName();//ファイル名を取得
            $move = $file->move('./upload/',$filename);//ファイルの移動
        }else{
            $filename = "";
        }
        
        //Eloquentモデル（登録処理）
        $books = new Book;
        $books -> user_id = Auth::user()->id;//追加のコード
        $books -> item_name = $request->item_name;
        $books -> item_number = $request->item_number;
        $books -> item_amount = $request->item_amount;
        $books -> item_img = $filename;
        $books -> published = $request->published;
        $books -> save();
        return redirect('/')->with('message', '本登録が完了しました');
    }
    //削除処理
    public function destroy(Book $book){
        $book->delete();
        return redirect('/');
    }
}

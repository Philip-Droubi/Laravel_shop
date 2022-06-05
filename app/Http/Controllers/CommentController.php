<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function index(Request $request, $id)
    {   //product id
        if ($product = Product::find($id)) {
            $comments = $product->comments()->orderByDesc('created_at')->paginate(25);
            if (!$comments->isEmpty()) {
                foreach ($comments as $comment) {
                    $data[] = [
                        "comment_id" => $comment->id,
                        "comment_txt" => $comment->txt,
                        "user_id" => $comment->user_id,
                        "user_name" => User::find($comment->user_id)->name,
                        "user_img" => User::find($comment->user_id)->prof_img_url,
                    ];
                }
                return response()->json(["comments" => $data], 200);
            }
            return response()->json([], 200);
        }
        return response()->json(["ُmessage" => __("messages.Sorry,Product not found :(")], 404);
    }

    public function store(Request $request, $id)
    {   //product id
        if (Product::find($id)) {
            $request->validate([
                'txt' => ['required', 'string'],
            ]);
            $comment = Comment::create([
                'product_id' => $id,
                'user_id' => Auth::id(),
                'txt' => $request->txt,
            ]);
            $user_img = User::find(Auth::id())->prof_img_url;
            $user_name = User::find(Auth::id())->name;
            return response()->json([ //respone may not go back to the front... may be 200 then front rerequset the index methode
                "comment" => $comment,
                "user_name" => $user_name,
                "user_img" => $user_img
            ], 200);
        }
        return response()->json(["ُmessage" => __("messages.Sorry,Product not found :(")], 404);
    }

    public function update(Request $request, $id)
    { //comment id
        $comment = Comment::find($id);
        if ($comment) {
            $request->validate([
                'txt' => ['string', 'nullable'],
            ]);
            if ($request->txt == null) {
                return response()->json([], 200); //no update happend
            }
            if (Gate::allows('Comment-Protection', $comment)) {
                $comment->update(['txt' => $request->txt]);
                return response()->json(["message" => "updated"], 200);
            }
            return response()->json(["message" => __("messages.You are not allowed to edit this comment")], 401);
        }
        return response()->json(["message" => __("messages.Comment not found")], 404);
    }

    public function destroy(Request $request, $id)
    { //comment id
        $comment = Comment::find($id);
        if ($comment) {
            if (Gate::allows('Comment-Protection', $comment)) {
                $comment->delete();
                return response()->json(["message" => "deleted"], 200);
            }
            return response()->json(["message" => __("messages.You are not allowed to edit this comment")], 401);
        }
        return response()->json(["message" => __("messages.Comment not found")], 404);
    }
}

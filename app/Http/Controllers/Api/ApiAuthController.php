<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\posts;
use App\Models\comments;
use Illuminate\Support\Facades\Validator;
use Auth;
class ApiAuthController extends Controller
{
    //

    public function register(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'min:6|required_with:password_confirmation|same:password_confirmation',
            'password_confirmation' => 'min:6',
            'phone' => 'required|min:10|unique:users'
        ],
        [
            'email.required' => 'Please provide email',
            'password.required' => 'Please provide password',
            'phone.required' => 'Please provide phone no',
            'phone.digits' => 'Please provide atleast 10 digits'
        ]);

        if ($validator->fails()){
            return response()->json(['status' => false,"message" => "Validation error",'errors' => $validator->messages()],400);
        }

        $user = new User();
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->password = bcrypt($request->password);
        $user->save();
        $token = $user->createToken('Api-token')->accessToken;
  
        return response()->json(["status" => true,'token' => $token,"message" => "User Registered Successfully"], 200);
    }
  
    /**
     * Login Req
     */
    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $accessToken = $user->createToken('Api-token')->accessToken;
            return response()->json(['user' => $user, 'access_token' => $accessToken]);
        } else {
            return response()->json(['error' => 'Invalid email or password'], 401);
        }
    }
    

    public function logout(){
        $accessToken = Auth::user()->token();
        $accessToken->revoke();
        return response()->json(['status' => true,'message' => 'Logged out successfully'],200);
    }
 
   

    public function getuserprofile()
    {
        $user = auth()->user();
        $userdetails["firstname"] = $user->first_name;
        $userdetails["lastname"] = $user->last_name;
        $userdetails["role"] = $user->role == 1 ? 'Writer' : ($user->role === 0 ? 'Editor' : null);
        return response()->json(['status' => true,"userprofiledetails" => $userdetails ],200);
    }

    public function storeuserprofile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|min:3',
            'lastname' => 'required|min:1',
            'role'=>'required|in:writer,editor'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $user = auth()->user();
        $user->first_name = $request->firstname;
        $user->last_name = $request->lastname;
        $user->role = $request->role=='writer'?1:0;
        $user->save();
        return response()->json(['status' => true,'message' => 'Profile Details Saved Successfully'],200);

    }

    public function createpost(Request $request)
    {
        $user = auth()->user();
        if($user->role == 1){
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'content' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        
        $post = new posts();
        $post->title =  $request->title;
        $post->content = $request->content;
        $post->user_id = $user->id;
        $post->save();
        return response()->json(['status' => true,'message' => 'Post Created Successfully','post'=>$post],200);
        }else{
            return response()->json(['error' => "You Dont Have Permission to create Post"], 401);
        }
    }

    public function updatepost(Request $request)
    {
        $user = auth()->user();
        if($user->role == 1){
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'content' => 'required',
                'postid'=>'required'
            ]);
    
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 401);
            }

        $post = posts::where(array('id'=>$request->postid,'user_id'=>$user->id))->first();
        if ($post) {
        $post->title = $request->title;
        $post->content = $request->content;
        $post->user_id = $user->id;
        $post->save();
        return response()->json(['status' => true,'message' => 'Post Updated Successfully','post'=>$post],200);
        } else {
        return response()->json(['error' => 'Post not found'], 404);
        }
        }else{
            return response()->json(['error' => "You Dont Have Permission to Edit Post"], 401);
        }
    }

    public function viewpost()
    {
        $user = auth()->user();
        if($user->role == 1){
            $post = posts::select('title','content','comment','first_name as commentedBy')
            ->leftjoin('comments','comments.post_id','=','posts.id')
            ->leftjoin('users','users.id','=','comments.user_id')
            ->where(array('posts.user_id'=>$user->id,'status'=>1))
            ->get();
            //dd($post);
            $postdetails = [];

            foreach ($post as $p) {
                $comment = array(
                    'comment' => $p->comment,
                    'commentedBy' => $p->commentedBy
                );

                if (!isset($postdetails[$p->title])) {
                    $postdetails[$p->title] = array(
                        'details' => array(
                            'title' => $p->title,
                            'content' => $p->content,
                            'comments' => array($comment)
                        )
                    );
                }else{
                    $postdetails[$p->title]['details']['comments'][] = $comment;
                } 
            }
            $postdetails = array_values($postdetails);
            return response()->json(['status' => true,'post'=>$postdetails],200);
        }else{
            $post = posts::select('title', 'content', 'comments.comment', 'users.first_name as commentedBy')
            ->leftJoin('comments', 'comments.post_id', '=', 'posts.id')
            ->leftJoin('users', 'users.id', '=', 'comments.user_id')
            ->where('status', 1)
            ->get();
            $postdetails = [];
            foreach ($post as $p) {
                $comment = array(
                    'comment' => $p->comment,
                    'commentedBy' => $p->commentedBy
                );
                if (!isset($postdetails[$p->title])) {
                    $postdetails[$p->title] = array(
                        'details' => array(
                            'title' => $p->title,
                            'content' => $p->content,
                            'comments' => array($comment)
                        )
                    );
                }else{
                    $postdetails[$p->title]['details']['comments'][] = $comment;
                } 
            }
            $postdetails = array_values($postdetails);
            return response()->json(['status' => true,'post'=>$postdetails],200);
        }
    }

    public function deletepost(Request $request)
    {
        $user = auth()->user();
        if($user->role == 1){
            $validator = Validator::make($request->all(), [
                'postid'=>'required'
            ]);
    
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 401);
            }

        $post = posts::where(array('id'=>$request->postid,'user_id'=>$user->id,'status'=>1))->first();
        if ($post) {
        $post->status = 0;
        $post->save();
        return response()->json(['status' => true,'message' => 'Post Deleted Successfully'],200);
        } else {
        return response()->json(['error' => 'Post not found'], 404);
        }

        }else{
            return response()->json(['error' => "You Dont Have Permission to Delete Post"], 401);
        }
    }

    public function addcommenttopost(Request $request)
    {
        $user = auth()->user();
        if($user->role == 0){
            $validator = Validator::make($request->all(), [
                'postid'=>'required',
                'comment'=>'required'
            ]);
    
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 401);
            }

                $comments = new comments();
                $comments->post_id = $request->postid;
                $comments->user_id = $user->id;
                $comments->comment = $request->comment;
                $comments->save();
                return response()->json(['status' => true,'message' => 'Comments Added Successfully'],200);

        }else{
            return response()->json(['error' => "You Dont Have Permission to Add Comments to Post"], 401);
        }
    }

   


}

<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;





class PostController extends Controller
{
    public function index()  {
        $posts = Post::latest()->paginate(5);
        return view('post.index', compact('posts'));


    }

    public function create() {
        return view('post.create');
    }

    public function store(Request $request): RedirectResponse {
        $this->validate($request, [
            'image' => 'required|image|mimes:jpeg,jpg,png|max:4000',
            'title' => 'required|min:5',
            'content' => 'required|min:10',
        ]);

        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        Post::create([
            'image' => $image->hashName(),
            'title' => $request->title,
            'content'=> $request->content,
        ]);

        return redirect()->route('posts.index')->with(['Succes' => 'Data Berhasil Di Simpan!']);
    }

    public function show(string $id) {
        $post = Post::findOrFail($id);
        return view('post\show', compact('post'));

    }

    public function edit(string $id)
    {
        //get post by ID
        $post = Post::findOrFail($id);

        //render view with post
        return view('post.edit', compact('post'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        //validate form
        $this->validate($request, [
            'image'     => 'image|mimes:jpeg,jpg,png|max:2048',
            'title'     => 'required|min:5',
            'content'   => 'required|min:10'
        ]);

        //get post by ID
        $post = Post::findOrFail($id);

        //check if image is uploaded
        if ($request->hasFile('image')) {

            //upload new image
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            //delete old image
            Storage::delete('public/posts/'.$post->image);

            //update post with new image
            $post->update([
                'image'     => $image->hashName(),
                'title'     => $request->title,
                'content'   => $request->content
            ]);

        } else {

            //update post without image
            $post->update([
                'title'     => $request->title,
                'content'   => $request->content
            ]);
        }

        //redirect to index
        return redirect()->route('posts.index')->with(['success' => 'Data Berhasil Diubah!']);
    }

    public function destroy($id): RedirectResponse
    {
        //get post by ID
        $post = Post::findOrFail($id);

        //delete image
        Storage::delete('public/posts/'. $post->image);

        //delete post
        $post->delete();

        //redirect to index
        return redirect()->route('posts.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }
}

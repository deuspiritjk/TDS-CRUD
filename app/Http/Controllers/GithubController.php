<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use Laravel\Socialite\Facades\Socialite;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Exception;

use App\Actions\Jetstream\CreateTeam;

class GithubController extends Controller
{
    public function githubpage()
    {
        return Socialite::driver('github')->redirect();
    }
    public function githubcallback()
    {
        try {
            $user = Socialite::driver('github')->user();
            $finduser = User::where('github_id', $user->id)->first();

            if ($finduser) {
                Auth::login($finduser);
                return redirect()->intended('/dashboard');
            } else {
                $newUser = User::updateOrCreate(['email' => $user->email],[
                    'name' => $user->nickname,
                    'email' => $user->email,
                    'github_id' => $user->id,
                    'password' => encrypt('123456dummy')
                ]);

                // Crear un nuevo equipo para el usuario
                // $team = app(CreateTeam::class)->create($newUser, ['name' => $user->name . "'s Team"]);
                $team = app(CreateTeam::class)->create($newUser, ['name' => 'Equipo de ' . explode(' ', $user->nickname, 2)[0]]);

                Auth::login($newUser);
                return redirect()->intended('/dashboard');
            }
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
}

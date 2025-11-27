<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea un utente amministratore';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creazione utente amministratore...');

        $name = $this->ask('Inserisci il nome:');
        $nickname = $this->ask('Inserisci il nickname:');
        $email = $this->ask('Inserisci l\'email:');
        $password = $this->secret('Inserisci la password:');
        $passwordConfirmation = $this->secret('Conferma la password:');

        // Validazione
        $validator = Validator::make([
            'name' => $name,
            'nickname' => $nickname,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'name' => 'required|string|max:255',
            'nickname' => 'required|string|max:255|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return 1;
        }

        // Creazione utente admin
        try {
            $user = User::create([
                'name' => $name,
                'nickname' => $nickname,
                'email' => $email,
                'password' => Hash::make($password),
                'is_admin' => true,
                'status' => 'approved'
            ]);

            $this->info('Utente amministratore creato con successo!');
            $this->info('ID: ' . $user->id);
            $this->info('Email: ' . $user->email);
            $this->info('Nickname: ' . $user->nickname);

        } catch (\Exception $e) {
            $this->error('Errore durante la creazione: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}

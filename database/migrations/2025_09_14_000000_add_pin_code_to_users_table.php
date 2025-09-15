<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pin_code', 4)->nullable()->unique()->after('qr_code');
        });

        // Backfill existing users with a unique 4-digit pin code
        $users = DB::table('users')->select('id', 'pin_code')->get();
        $used = collect(DB::table('users')->whereNotNull('pin_code')->pluck('pin_code'))->flip();

        foreach ($users as $user) {
            if ($user->pin_code) {
                continue;
            }
            do {
                $pin = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            } while ($used->has($pin));

            DB::table('users')->where('id', $user->id)->update(['pin_code' => $pin]);
            $used[$pin] = true;
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['pin_code']);
            $table->dropColumn('pin_code');
        });
    }
};

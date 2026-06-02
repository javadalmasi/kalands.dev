<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createAdminsTable();
        $this->alterUsersTable();
        $this->recreateCommentsTables();
        $this->alterBookmarksAndLikes();
        $this->createSupportTables();
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('affiliate_links');
        Schema::dropIfExists('queue_execution_logs');
        Schema::dropIfExists('system_configs');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('ticket_messages');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('ticket_categories');
        Schema::dropIfExists('password_reset_codes');
        Schema::dropIfExists('admins');
    }

    private function createAdminsTable(): void
    {
        if (Schema::hasTable('admins')) {
            return;
        }

        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 150);
            $table->string('username', 120)->unique();
            $table->string('email_address', 180)->nullable()->unique();
            $table->string('mobile_number', 20)->nullable()->unique();
            $table->text('password_hash');
            $table->string('password_salt', 64);
            $table->enum('access_level', [
                'super_admin',
                'content_manager',
                'system_manager',
                'user_manager',
            ])->default('content_manager');
            $table->boolean('is_active')->default(true);
            $table->string('dashboard_authkey', 64)->nullable()->index();
            $table->timestamp('dashboard_authkey_expires_at')->nullable();
            $table->text('two_factor_secret')->nullable();
            $table->json('two_factor_recovery_codes')->nullable();
            $table->string('theme_preference', 10)->default('light');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    private function alterUsersTable(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'password_hash')) {
                $table->text('password_hash')->nullable()->after('phone_verified_at');
            }
            if (!Schema::hasColumn('users', 'password_salt')) {
                $table->string('password_salt', 64)->nullable()->after('password_hash');
            }
            if (!Schema::hasColumn('users', 'two_factor_secret')) {
                $table->text('two_factor_secret')->nullable()->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'two_factor_recovery_codes')) {
                $table->json('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            }
            if (!Schema::hasColumn('users', 'dashboard_authkey')) {
                $table->string('dashboard_authkey', 64)->nullable()->index()->after('two_factor_recovery_codes');
            }
            if (!Schema::hasColumn('users', 'dashboard_authkey_expires_at')) {
                $table->timestamp('dashboard_authkey_expires_at')->nullable()->after('dashboard_authkey');
            }
            if (!Schema::hasColumn('users', 'theme_preference')) {
                $table->string('theme_preference', 10)->default('light')->after('dashboard_authkey_expires_at');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('theme_preference');
            }
            if (!Schema::hasColumn('users', 'profile_bio')) {
                $table->string('profile_bio', 500)->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('users', 'marketing_opt_in')) {
                $table->boolean('marketing_opt_in')->default(false)->after('profile_bio');
            }
        });

        if (Schema::hasColumn('users', 'password')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('password');
            });
        }

        if (Schema::hasColumn('users', 'is_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_admin');
            });
        }

        if (Schema::hasColumn('users', 'twofa_secret')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('twofa_secret');
            });
        }
    }

    private function recreateCommentsTables(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('comment_votes');
        Schema::dropIfExists('comments');
        Schema::enableForeignKeyConstraints();

        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name', 100)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('content');
            $table->string('ip_address', 45)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'spam'])->default('pending');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('parent_id')->references('id')->on('comments')->cascadeOnDelete();
        });
    }

    private function alterBookmarksAndLikes(): void
    {
        if (Schema::hasTable('bookmarks')) {
            Schema::table('bookmarks', function (Blueprint $table) {
                if (!Schema::hasColumn('bookmarks', 'category_name')) {
                    $table->string('category_name', 50)->default('عمومی')->after('product_id');
                }
                $table->unique(['user_id', 'product_id'], 'unique_bookmark');
            });
        }

        if (Schema::hasTable('likes')) {
            Schema::table('likes', function (Blueprint $table) {
                $table->unique(['user_id', 'product_id'], 'unique_like');
            });
        }
    }

    private function createSupportTables(): void
    {
        if (!Schema::hasTable('password_reset_codes')) {
            Schema::create('password_reset_codes', function (Blueprint $table) {
                $table->id();
                $table->string('identifier', 180)->unique();
                $table->string('code', 6);
                $table->timestamp('expires_at')->index();
            });
        }

        if (!Schema::hasTable('ticket_categories')) {
            Schema::create('ticket_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('slug', 100)->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tickets')) {
            Schema::create('tickets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('category_id');
                $table->string('subject', 200);
                $table->enum('status', ['open', 'answered', 'closed'])->default('open');
                $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->foreign('category_id')->references('id')->on('ticket_categories');
            });
        }

        if (!Schema::hasTable('ticket_messages')) {
            Schema::create('ticket_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ticket_id');
                $table->enum('sender_type', ['user', 'admin']);
                $table->unsignedBigInteger('sender_id');
                $table->text('message');
                $table->json('attachments')->nullable();
                $table->timestamps();

                $table->foreign('ticket_id')->references('id')->on('tickets')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('contact_messages')) {
            Schema::create('contact_messages', function (Blueprint $table) {
                $table->id();
                $table->string('name', 120);
                $table->string('email', 180);
                $table->string('subject', 200);
                $table->text('message');
                $table->string('ip_address', 45)->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('system_configs')) {
            Schema::create('system_configs', function (Blueprint $table) {
                $table->id();
                $table->string('config_key', 150)->unique();
                $table->json('config_value')->nullable();
                $table->boolean('is_sensitive')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('queue_execution_logs')) {
            Schema::create('queue_execution_logs', function (Blueprint $table) {
                $table->id();
                $table->timestamp('executed_at')->index();
                $table->enum('status', ['success', 'failed']);
                $table->text('error_message')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('affiliate_links')) {
            Schema::create('affiliate_links', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->unique();
                $table->text('link');
                $table->enum('status', ['active', 'error', 'expired'])->default('active');
                $table->text('error_message')->nullable();
                $table->timestamp('cached_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id();
                $table->string('actor_type', 20);
                $table->unsignedBigInteger('actor_id');
                $table->string('action', 200);
                $table->text('description')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }
    }
};

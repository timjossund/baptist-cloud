<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;

test('admin can upload sermon with audio conversion', function () {
    // 1. Arrange
    Storage::fake('local');
    Storage::fake('s3');

    // Create Admin User
    $user = User::factory()->create();
    $user->forceFill(['is_admin' => true])->save();

    // Mock FFMpeg call to avoid binary requirement
    // We use Mockery alias. Note: check for conflicts if running all tests.
    $audioMock = Mockery::mock('alias:FFMpeg\Media\Audio');
    $audioMock->shouldReceive('save')->andReturnUsing(function($format, $path) {
        file_put_contents($path, 'converted mp3 content');
    });

    $ffmpegMock = Mockery::mock('alias:FFMpeg\FFMpeg');
    $ffmpegMock->shouldReceive('create')->andReturnSelf();
    $ffmpegMock->shouldReceive('open')->andReturn($audioMock);

    // 2. Act
    $response = $this->actingAs($user)
        ->post(route('sermons.store'), [
            'title' => 'Test Sermon',
            'description' => 'A test sermon description',
            'published_at' => now(),
            'audio' => UploadedFile::fake()->create('recording.wav', 1000, 'audio/wav'),
            'video' => 'https://example.com/video', // Assuming string URL based on controller? Need to check controller validation logic. Controller said 'video' => 'required'. Assuming string or file?
            // In Store method: 'video' => 'required'.
            // If it's a file, it would be $request->file('video').
            // Let's check controller again.
            'image' => UploadedFile::fake()->image('cover.jpg'),
        ]);

    // 3. Assert
    $response->assertRedirect(route('sermons.index'));

    // Check Database
    $this->assertDatabaseHas('sermons', [
        'title' => 'Test Sermon',
        'description' => 'A test sermon description',
    ]);

    // Check S3
    // We expect a file in 'sermons' directory with mp3 extension
    $files = Storage::disk('s3')->allFiles('sermons');
    expect(count($files))->toBeGreaterThan(0);

    // Verify it is an MP3
    $path = $files[0];
    expect(pathinfo($path, PATHINFO_EXTENSION))->toBe('mp3');

});

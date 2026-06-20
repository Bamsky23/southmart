<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocController extends Controller
{
    /**
     * Display documentation home (default topic: arsitektur-sistem).
     */
    public function index()
    {
        return redirect()->route('doc.show', ['topic' => 'arsitektur-sistem']);
    }

    /**
     * Show specific documentation topic.
     */
    public function show(string $topic)
    {
        $topics = [
            'arsitektur-sistem' => '1. Arsitektur Sistem',
            'diagram-node' => '2. Diagram Node',
            'fragmentasi-horizontal' => '3. Fragmentasi Horizontal',
            'replikasi-data' => '4. Replikasi Data',
            'alur-distribusi-data' => '5. Alur Distribusi Data',
            'query-lintas-node' => '6. Query Lintas Node',
            'pengujian-konsistensi' => '7. Pengujian Konsistensi',
            'panduan-instalasi' => '8. Panduan Instalasi',
            'panduan-pengujian' => '9. Panduan Pengujian',
            'dokumentasi-screenshots' => '10. Panduan Dokumentasi & Screenshots',
        ];

        if (!array_key_exists($topic, $topics)) {
            abort(404, 'Topik dokumentasi tidak ditemukan.');
        }

        $activeTopic = $topic;
        $title = $topics[$topic];

        return view('doc.show', compact('topics', 'activeTopic', 'title'));
    }
}

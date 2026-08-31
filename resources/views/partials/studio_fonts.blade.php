@php
    $dynamicMediaFontsList = [];
    $mediaUploadPath = public_path('uploads/studio');
    if (\Illuminate\Support\Facades\File::exists($mediaUploadPath)) {
        $fontExts = ['ttf', 'otf', 'woff', 'woff2'];
        foreach (\Illuminate\Support\Facades\File::files($mediaUploadPath) as $f) {
            $ext = strtolower($f->getExtension());
            if (in_array($ext, $fontExts)) {
                $rawName = pathinfo($f->getFilename(), PATHINFO_FILENAME);
                $cleanName = preg_replace('/^\d+_/', '', $rawName);
                $cleanName = trim(str_replace(['_', '-'], ' ', $cleanName));
                $dynamicMediaFontsList[] = [
                    'family' => $cleanName,
                    'url'    => asset('uploads/studio/' . $f->getFilename())
                ];
            }
        }
    }
@endphp

// ==========================================
// 🅰️ ফন্ট কনফিগারেশন (নতুন ফন্ট এখানে অ্যাড করুন)
// ==========================================
const STUDIO_FONTS = {
    google: [
        'Hind Siliguri:300,400,500,600,700', 'Noto Sans Bengali:400,700', 
        'Baloo Da 2:400,500,600,700', 'Galada', 'Anek Bangla:400,600,800', 
        'Tiro Bangla', 'Mina', 'Noto Serif Bengali:400,700', 
        'Atma:300,400,500,600,700', 'Noto Serif Bengali Condensed'
    ],
    local: [
        'Noto Serif Cond Thin', 'Noto Serif Cond ExtraLight', 'Noto Serif Cond Light', 
        'Noto Serif Cond Regular', 'Noto Serif Cond Medium', 'Noto Serif Cond SemiBold', 
        'Noto Serif Cond Bold', 'Noto Serif Cond ExtraBold', 'Noto Serif Cond Black',
        'Noto Serif Bengali SemiBold', 'SolaimanLipi', 'Li Alinur Banglaborno', 
        'Li Alinur Kuyasha', 'Li Alinur Sangbadpatra', 'Li Alinur Tumatul',
        'Li MA Hai', 'Li Purno Pran', 'Li Sabbir Sorolota', 'Li Shohid Abu Sayed',
        'Li Abu JM Akkas', 'Li Mehdi Ekushey', 'Li Shadhinata', 'NotoSerifBengali-Regular', 'SutonnyOMJRegular',
        @foreach($dynamicMediaFontsList as $dmf)
            '{{ $dmf['family'] }}',
        @endforeach
    ]
};

const fontSutonny = new FontFace("SutonnyOMJRegular", "url(/fonts/SutonnyOMJRegular.ttf)");
document.fonts.add(fontSutonny);

@foreach($dynamicMediaFontsList as $dmf)
try {
    const font_{{ md5($dmf['family']) }} = new FontFace("{{ $dmf['family'] }}", "url('{{ $dmf['url'] }}')");
    document.fonts.add(font_{{ md5($dmf['family']) }});
} catch(e) { console.warn("Failed loading dynamic font:", "{{ $dmf['family'] }}", e); }
@endforeach
<?php

namespace App\Services;

use App\Models\Feature;
use Illuminate\Support\Str;

class FeatureExtractionService
{
    protected DirectionExtractionService $directionService;

    public function __construct()
    {
        $this->directionService = new DirectionExtractionService();
    }

    /**
     * Normalized feature groups with their standard features
     * Uses word boundaries to prevent false positives
     */
    protected array $featureGroups = [
        // 1. Basic Info (excluding direction - handled separately)
        'basic_info' => [
            'so_phong_ngu' => ['phòng ngủ', 'pn ', 'số phòng ngủ', 'có \d+ pn'],
            'so_wc' => ['wc', 'nhà vệ sinh', 'toilet', 'phòng vệ sinh'],
            'so_tang' => ['tầng', 'lầu', 'số tầng'],
        ],

        // 2. Location Features - require proximity keywords
        'location' => [
            'gan_cho' => ['gần chợ', 'kề chợ', 'cạnh chợ', 'chợ gần', 'gần chợ'],
            'gan_truong' => ['gần trường', 'trường học', 'trường mầm non', 'kề trường'],
            'gan_benh_vien' => ['gần bệnh viện', 'kề bệnh viện', 'bệnh viện gần'],
            'gan_trung_tam' => ['gần trung tâm', 'kề trung tâm', 'trung tâm thành phố', 'trung tâm quận'],
            'gan_cong_vien' => ['gần công viên', 'công viên gần'],
            'gan_ho_song' => ['gần hồ', 'gần sông', 'view sông', 'bờ sông', 'hồ cá'],
            'gan_quoc_lo' => ['gần quốc lộ', 'kề quốc lộ', 'dọc quốc lộ'],
            'gan_khu_cong_nghiep' => ['gần khu công nghiệp', 'kề khu công nghiệp', 'kcn'],
            'gan_dai_hoc' => ['gần đại học', 'trường đại học'],
            'gan_trung_tam_thuong_mai' => ['trung tâm thương mại', 'siêu thị'],
        ],

        // 3. Traffic & Infrastructure
        'infrastructure' => [
            'duong_oto' => ['đường ô tô', 'ô tô tránh nhau', 'xe hơi', 'oto', 'ô tô'],
            'duong_nhua' => ['đường nhựa', 'áp phan', 'asphalt', 'btnm'],
            'duong_thong' => ['đường thông', 'thông thoáng'],
            'mat_duong' => ['mặt đường', 'mặt tiền đường'],
            'vh_hoi' => ['vỉa hè', 'có vỉa hè'],
            'dien_nuoc' => ['điện nước', 'điện đầy đủ', 'nước máy'],
            'quy_hoach' => ['quy hoạch', 'sắp mở đường'],
            'ha_tang_dong_bo' => ['hạ tầng đồng bộ', 'đồng bộ'],
        ],

        // 4. Legal Status
        'legal' => [
            'co_so_do' => ['sổ đỏ', 'bìa đỏ', 'sổ hồng'],
            'chinh_chu' => ['chính chủ'],
            'full_tho_cu' => ['full thổ cư', 'thổ cư đầy đủ'],
            'phap_ly' => ['pháp lý rõ ràng', 'pháp lý'],
            'sang_ten' => ['sang tên ngay', 'sang tên'],
            'tach_thua' => ['tách thửa', 'tách thổ'],
        ],

        // 5. Land Features
        'land' => [
            'vuong_vac' => ['vuông vắn', 'hình vuông'],
            'no_hau' => ['nở hậu', 'nở phía sau'],
            'cao_rao' => ['cao ráo', 'đất cao'],
            'khong_ngap' => ['không ngập', 'chống ngập', 'không bị ngập'],
            'lo_goc' => ['lô góc', 'góc đường', 'mặt tiền góc'],
            '2_mat_tien' => ['2 mặt tiền', 'hai mặt tiền'],
            '3_mat_tien' => ['3 mặt tiền', 'ba mặt tiền'],
            'thoang' => ['thoáng', 'thông thoáng', 'thoáng mát'],
            'view_dep' => ['view đẹp', 'tầm nhìn đẹp', 'cảnh đẹp'],
            'the_dat_dep' => ['thế đất đẹp', 'thế đất'],
        ],

        // 6. House Features
        'house' => [
            'garage' => ['gara', 'chỗ đỗ xe', 'bãi xe'],
            'san_vuon' => ['sân vườn', 'vườn'],
            'ho_ca' => ['hồ cá', 'hồ cá kiểng'],
            'ban_cong' => ['ban công', 'logia'],
            'phong_tho' => ['phòng thờ', 'bàn thờ'],
            'gac_lung' => ['gác lửng', 'lửng'],
            'noi_that' => ['nội thất', 'full nội thất'],
            'nha_moi' => ['nhà mới', 'mới xây', 'xây mới'],
            'o_ngay' => ['có thể ở ngay', 'ở được ngay', 'về ở ngay'],
            'khep_kin' => ['khép kín', 'kín đáo'],
            'nha_cap_4' => ['cấp 4', 'nhà cấp 4'],
        ],

        // 7. Investment Potential
        'investment' => [
            'kinh_doanh_tot' => ['kinh doanh tốt', 'buôn bán'],
            'thanh_khoan_cao' => ['thanh khoản cao', 'thanh khoản', 'dễ bán'],
            'tang_gia' => ['tiềm năng tăng giá', 'tăng giá'],
            'dau_tu_sinh_loi' => ['đầu tư sinh lời', 'đầu tư'],
            'giu_tien' => ['giữ tiền', 'bảo toàn vốn'],
            'cho_thue_tot' => ['cho thuê tốt', 'cho thuê', 'dịch vụ'],
            'mat_tien_kinh_doanh' => ['mặt tiền kinh doanh'],
        ],

        // 8. Living Environment
        'environment' => [
            'dan_tri_cao' => ['dân trí cao', 'cư dân tri thức'],
            'an_ninh_tot' => ['an ninh', 'an ninh tốt', 'bảo vệ'],
            'yen_tinh' => ['yên tĩnh', 'im ắng', 'không ồn'],
            'khu_cao_cap' => ['khu cao cấp', 'cao cấp', 'đẳng cấp'],
            'dong_dan_cu' => ['đông dân cư', 'dân cư đông'],
        ],

        // 9. Suitable For
        'suitable_for' => [
            'phu_hop_o' => ['phù hợp ở', 'để ở', 'phù hợp sinh sống'],
            'phu_hop_dau_tu' => ['phù hợp đầu tư', 'đầu tư tốt'],
            'phu_hop_nha_vuon' => ['nhà vườn', 'homestay', 'farmstay'],
            'phu_hop_kho_xuong' => ['kho xưởng', 'kho', 'xưởng'],
            'phu_hop_kinh_doanh' => ['mở quán', 'văn phòng', 'spa', 'cafe'],
        ],

        // 10. Property Status
        'status' => [
            'dang_hoan_thien' => ['đang hoàn thiện', 'đang xây dựng', 'đang thi công'],
            'da_xay_xong' => ['đã xây xong', 'xây xong'],
            'co_nha_san' => ['có nhà sẵn', 'nhà có sẵn'],
            'dang_cho_thue' => ['đang cho thuê', 'đang được thuê', 'có người thuê'],
        ],

        // 11. Directions - extracted separately by DirectionExtractionService
        'direction' => [],
    ];

    /**
     * Negative context patterns - when these appear, suppress related features
     */
    protected array $negativeContextPatterns = [
        'không' => ['không', 'chưa', 'chẳng', 'đâu có'],
        'gần' => ['xa', 'không gần', 'chẳng gần'],
    ];

    /**
     * Words that indicate the feature is NOT present
     */
    protected array $negationWords = ['không', 'chưa', 'chẳng', 'đâu', 'không có', 'chẳng có', 'chưa có'];

    /**
     * Normalize raw features from AI extraction
     * Only extracts features that are actually MENTIONED in the text
     * Returns standardized feature codes
     */
    public function normalizeFeatures(array $rawFeatures, string $rawText = ''): array
    {
        $normalized = [];
        $text = '';

        // Combine all raw features into text for searching
        if (is_array($rawFeatures)) {
            $text = mb_strtolower(implode(' ', $rawFeatures));
        } else {
            $text = mb_strtolower((string) $rawFeatures);
        }

        // If raw text is provided, append it for better extraction
        if ($rawText) {
            $text .= ' ' . mb_strtolower($rawText);
        }

        // Remove emojis and normalize
        $text = $this->cleanText($text);

        // First pass: keywordMappings with negation checking
        foreach ($this->keywordMappings as $keyword => $code) {
            if ($this->hasPositiveMention($text, $keyword)) {
                $normalized[] = $code;
            }
        }

        // Second pass: featureGroups with negation checking
        foreach ($this->featureGroups as $group => $features) {
            // Skip direction group - handled separately
            if ($group === 'direction') {
                continue;
            }

            foreach ($features as $code => $keywords) {
                $fullCode = "{$group}:{$code}";
                if (in_array($fullCode, $normalized)) {
                    continue;
                }

                if ($this->hasPositiveFeatureMention($text, $keywords)) {
                    $normalized[] = $fullCode;
                }
            }
        }

        // Extract directions using specialized service
        $directions = $this->directionService->extract($text);
        foreach ($directions as $dir) {
            $fullCode = "direction:{$dir}";
            if (!in_array($fullCode, $normalized)) {
                $normalized[] = $fullCode;
            }
        }

        return array_values(array_unique($normalized));
    }

    /**
     * Check if text has a positive mention of a keyword (for keywordMappings)
     */
    protected function hasPositiveMention(string $text, string $keyword): bool
    {
        $pos = mb_strpos($text, $keyword);

        if ($pos === false) {
            return false;
        }

        // Check if there's negation before this keyword
        return !$this->hasNegationBeforeKeyword($text, $pos, $keyword);
    }

    /**
     * Clean text by removing emojis and normalizing whitespace
     */
    protected function cleanText(string $text): string
    {
        // Remove emojis
        $text = preg_replace(
            '/[\x{1F600}-\x{1F64F}'
            . '\x{1F300}-\x{1F5FF}'
            . '\x{1F680}-\x{1F6FF}'
            . '\x{1F1E0}-\x{1F1FF}'
            . '\x{2600}-\x{26FF}]/u',
            '',
            $text
        );

        // Normalize whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    /**
     * Check if text has a positive mention of the feature
     * Only returns true if the keyword appears with positive context
     */
    protected function hasPositiveFeatureMention(string $text, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            // Skip regex patterns (containing special chars like \d)
            if (strpos($keyword, '\\') !== false) {
                if (preg_match('/' . $keyword . '/iu', $text)) {
                    // Check negation around this match
                    if (!$this->hasNegationNearMatch($text, $keyword)) {
                        return true;
                    }
                }
                continue;
            }

            // Find position of keyword in text
            $pos = mb_strpos($text, $keyword);

            if ($pos !== false) {
                // Check if there's negation before this keyword
                if ($this->hasNegationBeforeKeyword($text, $pos, $keyword)) {
                    continue; // Skip this keyword, try others
                }

                // This keyword is a positive match
                return true;
            }
        }

        return false;
    }

    /**
     * Check if there's negation before the keyword
     */
    protected function hasNegationBeforeKeyword(string $text, int $keywordPos, string $keyword): bool
    {
        // Get text before keyword (look back ~50 chars)
        $beforePos = max(0, $keywordPos - 50);
        $textBefore = mb_substr($text, $beforePos, $keywordPos - $beforePos);

        foreach ($this->negationWords as $negation) {
            // Check if negation word appears close to the keyword
            if (mb_strpos($textBefore, $negation) !== false) {
                // Verify it's within reasonable distance (words, not just anywhere)
                $words = preg_split('/\s+/', $textBefore);
                $lastFewWords = array_slice($words, -5);
                if (in_array($negation, $lastFewWords) || in_array(mb_strtolower($negation), $lastFewWords)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if there's negation near a regex match
     */
    protected function hasNegationNearMatch(string $text, string $pattern): bool
    {
        // Find all matches
        if (!preg_match_all('/' . $pattern . '/iu', $text, $matches, PREG_OFFSET_CAPTURE)) {
            return false;
        }

        foreach ($matches[0] as $match) {
            $pos = $match[1];
            $beforePos = max(0, $pos - 50);
            $textBefore = mb_substr($text, $beforePos, $pos - $beforePos);

            $words = preg_split('/\s+/', trim($textBefore));
            $lastFewWords = array_slice($words, -5);

            foreach ($this->negationWords as $negation) {
                if (in_array(mb_strtolower($negation), $lastFewWords)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Direct keyword mappings for simple normalization
     * Only includes unambiguous keywords (not single chars or too generic)
     */
    protected array $keywordMappings = [
        // Legal
        'sổ đỏ' => 'legal:co_so_do',
        'sổ hồng' => 'legal:co_so_do',
        'bìa đỏ' => 'legal:co_so_do',
        'chính chủ' => 'legal:chinh_chu',
        'pháp lý rõ ràng' => 'legal:phap_ly',
        'pháp lý' => 'legal:phap_ly',
        'sang tên ngay' => 'legal:sang_ten',
        'tách thửa' => 'legal:tach_thua',

        // Location
        'gần chợ' => 'location:gan_cho',
        'kề chợ' => 'location:gan_cho',
        'cạnh chợ' => 'location:gan_cho',
        'gần trường' => 'location:gan_truong',
        'trường học' => 'location:gan_truong',
        'trường mầm non' => 'location:gan_truong',
        'gần bệnh viện' => 'location:gan_benh_vien',
        'gần công viên' => 'location:gan_cong_vien',
        'gần hồ' => 'location:gan_ho_song',
        'gần sông' => 'location:gan_ho_song',
        'bờ sông' => 'location:gan_ho_song',
        'gần quốc lộ' => 'location:gan_quoc_lo',
        'gần khu công nghiệp' => 'location:gan_khu_cong_nghiep',
        'khu công nghiệp' => 'location:gan_khu_cong_nghiep',
        'gần trung tâm' => 'location:gan_trung_tam',
        'trung tâm thành phố' => 'location:gan_trung_tam',
        'gần đại học' => 'location:gan_dai_hoc',
        'trung tâm thương mại' => 'location:gan_trung_tam_thuong_mai',

        // Infrastructure
        'mặt đường' => 'infrastructure:mat_duong',
        'đường nhựa' => 'infrastructure:duong_nhua',
        'áp phan' => 'infrastructure:duong_nhua',
        'ô tô tránh nhau' => 'infrastructure:duong_oto',
        'xe hơi' => 'infrastructure:duong_oto',
        'điện nước' => 'infrastructure:dien_nuoc',
        'quy hoạch' => 'infrastructure:quy_hoach',

        // Land
        'vuông vắn' => 'land:vuong_vac',
        'nở hậu' => 'land:no_hau',
        'cao ráo' => 'land:cao_rao',
        'không ngập' => 'land:khong_ngap',
        'lô góc' => 'land:lo_goc',
        'góc đường' => 'land:lo_goc',
        'mặt tiền góc' => 'land:lo_goc',
        '2 mặt tiền' => 'land:2_mat_tien',
        'hai mặt tiền' => 'land:2_mat_tien',
        '3 mặt tiền' => 'land:3_mat_tien',
        'thông thoáng' => 'land:thoang',
        'view đẹp' => 'land:view_dep',

        // House
        'gara' => 'house:garage',
        'sân vườn' => 'house:san_vuon',
        'nội thất' => 'house:noi_that',
        'full nội thất' => 'house:noi_that',
        'nhà mới' => 'house:nha_moi',
        'mới xây' => 'house:nha_moi',
        'ở được ngay' => 'house:o_ngay',
        'về ở ngay' => 'house:o_ngay',
        'có thể ở ngay' => 'house:o_ngay',
        'khép kín' => 'house:khep_kin',
        'cấp 4' => 'house:nha_cap_4',
        'nhà cấp 4' => 'house:nha_cap_4',
        'ban công' => 'house:ban_cong',
        'gác lửng' => 'house:gac_lung',

        // Investment
        'kinh doanh tốt' => 'investment:kinh_doanh_tot',
        'thanh khoản cao' => 'investment:thanh_khoan_cao',
        'tiềm năng tăng giá' => 'investment:tang_gia',
        'đầu tư sinh lời' => 'investment:dau_tu_sinh_loi',
        'cho thuê' => 'investment:cho_thue_tot',

        // Environment
        'an ninh tốt' => 'environment:an_ninh_tot',
        'yên tĩnh' => 'environment:yen_tinh',
        'khu cao cấp' => 'environment:khu_cao_cap',
        'đông dân cư' => 'environment:dong_dan_cu',

        // Status
        'đang hoàn thiện' => 'status:dang_hoan_thien',
        'đã xây xong' => 'status:da_xay_xong',
        'nhà có sẵn' => 'status:co_nha_san',
    ];

    /**
     * Create or get Feature models from normalized codes
     */
    public function syncFeatures(array $normalizedCodes, $post): array
    {
        $featureIds = [];

        foreach ($normalizedCodes as $code) {
            // Parse group:code format
            $parts = explode(':', $code, 2);
            $group = $parts[0] ?? 'other';
            $slug = $parts[1] ?? $code;

            // Create human-readable name
            $name = $this->getFeatureName($slug, $group);

            // Create feature if not exists
            $feature = Feature::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name]
            );

            $featureIds[] = $feature->id;
        }

        // Sync to pivot table
        $post->features()->sync($featureIds);

        return $featureIds;
    }

    /**
     * Convert slug to human-readable name
     */
    protected function getFeatureName(string $slug, string $group = ''): string
    {
        $names = [
            // Basic
            'so_phong_ngu' => 'Số phòng ngủ',
            'so_wc' => 'Số WC',
            'so_tang' => 'Số tầng',

            // Directions
            'dong' => 'Hướng Đông',
            'tay' => 'Hướng Tây',
            'nam' => 'Hướng Nam',
            'bac' => 'Hướng Bắc',
            'dong_nam' => 'Hướng Đông Nam',
            'dong_bac' => 'Hướng Đông Bắc',
            'tay_nam' => 'Hướng Tây Nam',
            'tay_bac' => 'Hướng Tây Bắc',

            // Location
            'gan_cho' => 'Gần chợ',
            'gan_truong' => 'Gần trường học',
            'gan_benh_vien' => 'Gần bệnh viện',
            'gan_trung_tam' => 'Gần trung tâm',
            'gan_cong_vien' => 'Gần công viên',
            'gan_ho_song' => 'Gần hồ/sông',
            'gan_quoc_lo' => 'Gần quốc lộ',
            'gan_khu_cong_nghiep' => 'Gần khu công nghiệp',
            'gan_dai_hoc' => 'Gần đại học',
            'gan_trung_tam_thuong_mai' => 'Gần trung tâm thương mại',

            // Infrastructure
            'duong_oto' => 'Đường ô tô',
            'duong_nhua' => 'Đường nhựa',
            'duong_thong' => 'Đường thông thoáng',
            'mat_duong' => 'Mặt đường',
            'vh_hoi' => 'Vỉa hè',
            'dien_nuoc' => 'Điện nước đầy đủ',
            'quy_hoach' => 'Quy hoạch',
            'ha_tang_dong_bo' => 'Hạ tầng đồng bộ',

            // Legal
            'co_so_do' => 'Có sổ đỏ',
            'chinh_chu' => 'Chính chủ',
            'full_tho_cu' => 'Full thổ cư',
            'phap_ly' => 'Pháp lý rõ ràng',
            'sang_ten' => 'Sang tên ngay',
            'tach_thua' => 'Tách thửa',

            // Land
            'vuong_vac' => 'Vuông vắn',
            'no_hau' => 'Nở hậu',
            'cao_rao' => 'Cao ráo',
            'khong_ngap' => 'Không ngập',
            'lo_goc' => 'Lô góc',
            '2_mat_tien' => '2 mặt tiền',
            '3_mat_tien' => '3 mặt tiền',
            'thoang' => 'Thoáng',
            'view_dep' => 'View đẹp',
            'the_dat_dep' => 'Thế đất đẹp',

            // House
            'garage' => 'Gara ô tô',
            'san_vuon' => 'Sân vườn',
            'ho_ca' => 'Hồ cá',
            'ban_cong' => 'Ban công',
            'phong_tho' => 'Phòng thờ',
            'gac_lung' => 'Gác lửng',
            'noi_that' => 'Nội thất',
            'nha_moi' => 'Nhà mới',
            'o_ngay' => 'Có thể ở ngay',
            'khep_kin' => 'Khép kín',
            'nha_cap_4' => 'Nhà cấp 4',

            // Investment
            'kinh_doanh_tot' => 'Kinh doanh tốt',
            'thanh_khoan_cao' => 'Thanh khoản cao',
            'tang_gia' => 'Tiềm năng tăng giá',
            'dau_tu_sinh_loi' => 'Đầu tư sinh lời',
            'giu_tien' => 'Giữ tiền',
            'cho_thue_tot' => 'Cho thuê tốt',
            'mat_tien_kinh_doanh' => 'Mặt tiền kinh doanh',

            // Environment
            'dan_tri_cao' => 'Dân trí cao',
            'an_ninh_tot' => 'An ninh tốt',
            'yen_tinh' => 'Yên tĩnh',
            'khu_cao_cap' => 'Khu cao cấp',
            'dong_dan_cu' => 'Đông dân cư',

            // Suitable for
            'phu_hop_o' => 'Phù hợp để ở',
            'phu_hop_dau_tu' => 'Phù hợp đầu tư',
            'phu_hop_nha_vuon' => 'Phù hợp nhà vườn',
            'phu_hop_kho_xuong' => 'Phù hợp kho xưởng',
            'phu_hop_kinh_doanh' => 'Phù hợp kinh doanh',

            // Status
            'dang_hoan_thien' => 'Đang hoàn thiện',
            'da_xay_xong' => 'Đã xây xong',
            'co_nha_san' => 'Có nhà sẵn',
            'dang_cho_thue' => 'Đang cho thuê',
        ];

        return $names[$slug] ?? ucfirst(str_replace('_', ' ', $slug));
    }

    /**
     * Get feature group display name
     */
    public function getGroupName(string $group): string
    {
        $names = [
            'basic_info' => 'Thông tin cơ bản',
            'location' => 'Vị trí',
            'infrastructure' => 'Giao thông & Hạ tầng',
            'legal' => 'Pháp lý',
            'land' => 'Đặc điểm đất',
            'house' => 'Đặc điểm nhà',
            'investment' => 'Tiềm năng đầu tư',
            'environment' => 'Môi trường sống',
            'suitable_for' => 'Phù hợp cho',
            'status' => 'Trạng thái',
            'direction' => 'Hướng nhà',
        ];

        return $names[$group] ?? $group;
    }

    /**
     * Extract directions from raw text using specialized service
     */
    public function extractDirections(string $text): array
    {
        return $this->directionService->extract($text);
    }

    /**
     * Extract directions with full metadata
     */
    public function extractDirectionsWithConfidence(string $text): array
    {
        return $this->directionService->extractWithConfidence($text);
    }
}

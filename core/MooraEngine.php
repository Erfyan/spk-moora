<?php
/**
 * MOORA (Multi-Objective Optimization on the basis of Ratio Analysis) Engine
 * 
 * Implementasi Matematis Metode MOORA (Brauers & Zavadskas, 2006)
 * Dirancang khusus untuk Reproducibility & Analisis Riset Jurnal Ilmiah
 * 
 * @package Core
 * @author Tim Riset SPK MOORA
 */

declare(strict_types=1);

namespace Core;

class MooraEngine
{
    /**
     * Menjalankan kalkulasi lengkap MOORA dengan pencatatan audit matematis di setiap tahap
     * 
     * @param array $alternatives Daftar alternatif [['id' => 1, 'kode' => 'A1', 'nama' => '...'], ...]
     * @param array $criteria     Daftar kriteria [['id' => 1, 'kode' => 'C1', 'nama' => '...', 'bobot' => 25, 'jenis' => 'benefit'], ...]
     * @param array $rawMatrix    Matriks skor [alt_id => [crit_id => nilai, ...], ...]
     * @return array Hasil kalkulasi komprehensif MOORA
     */
    public function calculate(array $alternatives, array $criteria, array $rawMatrix): array
    {
        $result = [
            'has_data'           => false,
            'alternatives'       => $alternatives,
            'criteria'           => [],
            'decision_matrix'    => [],
            'denominators'       => [],
            'normalized_matrix'  => [],
            'weighted_matrix'    => [],
            'benefit_sums'       => [],
            'cost_sums'          => [],
            'scores'             => [],
            'ranking'            => []
        ];

        if (empty($alternatives) || empty($criteria)) {
            return $result;
        }

        $result['has_data'] = true;

        // 1. Normalisasi Bobot Kriteria (w_j / total_bobot)
        $totalWeight = 0.0;
        foreach ($criteria as $c) {
            $totalWeight += (float)$c['bobot'];
        }

        $criteriaMap = [];
        $normalizedWeights = [];
        foreach ($criteria as $c) {
            $critId = (int)$c['id'];
            $w = $totalWeight > 0 ? (float)$c['bobot'] / $totalWeight : (1.0 / count($criteria));
            $c['normalized_weight'] = $w;
            $criteriaMap[$critId] = $c;
            $normalizedWeights[$critId] = $w;
        }
        $result['criteria'] = $criteriaMap;

        // 2. Pembentukan Matriks Keputusan (X)
        $X = [];
        foreach ($alternatives as $alt) {
            $altId = (int)$alt['id'];
            $X[$altId] = [];
            foreach ($criteriaMap as $critId => $c) {
                $val = isset($rawMatrix[$altId][$critId]) ? (float)$rawMatrix[$altId][$critId] : 0.0;
                $X[$altId][$critId] = $val;
            }
        }
        $result['decision_matrix'] = $X;

        // 3. Tahap 1: Normalisasi Matriks Rasio (x*_ij = x_ij / sqrt(sum(x^2_ij)))
        $denominators = [];
        foreach ($criteriaMap as $critId => $c) {
            $sumSquares = 0.0;
            foreach ($alternatives as $alt) {
                $altId = (int)$alt['id'];
                $val = $X[$altId][$critId] ?? 0.0;
                $sumSquares += pow($val, 2);
            }
            $denominators[$critId] = $sumSquares > 0 ? sqrt($sumSquares) : 0.0;
        }
        $result['denominators'] = $denominators;

        $X_norm = [];
        foreach ($alternatives as $alt) {
            $altId = (int)$alt['id'];
            $X_norm[$altId] = [];
            foreach ($criteriaMap as $critId => $c) {
                $denom = $denominators[$critId];
                $val = $X[$altId][$critId];
                $X_norm[$altId][$critId] = ($denom > 0) ? ($val / $denom) : 0.0;
            }
        }
        $result['normalized_matrix'] = $X_norm;

        // 4. Tahap 2: Normalisasi Matriks Terbobot (v_ij = w_j * x*_ij)
        $V = [];
        foreach ($alternatives as $alt) {
            $altId = (int)$alt['id'];
            $V[$altId] = [];
            foreach ($criteriaMap as $critId => $c) {
                $wj = $normalizedWeights[$critId];
                $rij = $X_norm[$altId][$critId];
                $V[$altId][$critId] = $wj * $rij;
            }
        }
        $result['weighted_matrix'] = $V;

        // 5. Tahap 3: Perhitungan Optimasi Multi-Objektif (Y_i = Sum(Benefit) - Sum(Cost))
        $benefitSums = [];
        $costSums = [];
        $scores = [];

        foreach ($alternatives as $alt) {
            $altId = (int)$alt['id'];
            $sumBenefit = 0.0;
            $sumCost = 0.0;

            foreach ($criteriaMap as $critId => $c) {
                $weightedVal = $V[$altId][$critId];
                if (strtolower($c['jenis']) === 'cost') {
                    $sumCost += $weightedVal;
                } else {
                    $sumBenefit += $weightedVal;
                }
            }

            $score = $sumBenefit - $sumCost;

            $benefitSums[$altId] = $sumBenefit;
            $costSums[$altId]    = $sumCost;
            $scores[$altId]       = $score;
        }

        $result['benefit_sums'] = $benefitSums;
        $result['cost_sums']    = $costSums;
        $result['scores']       = $scores;

        // 6. Tahap 4: Pemeringkatan (Ranking dari skor tertinggi ke terendah)
        $rankingData = [];
        foreach ($alternatives as $alt) {
            $altId = (int)$alt['id'];
            $rankingData[] = [
                'id'          => $altId,
                'kode'        => $alt['kode'],
                'nama'        => $alt['nama'],
                'keterangan'  => $alt['keterangan'] ?? '',
                'benefit_sum' => $benefitSums[$altId],
                'cost_sum'    => $costSums[$altId],
                'score'       => $scores[$altId],
            ];
        }

        // Sort descending berdasarkan nilai score
        usort($rankingData, function ($a, $b) {
            if ($a['score'] == $b['score']) {
                return 0;
            }
            return ($a['score'] < $b['score']) ? 1 : -1;
        });

        // Tetapkan nomor ranking
        $ranked = [];
        $rank = 1;
        foreach ($rankingData as $item) {
            $item['rank'] = $rank++;
            $ranked[] = $item;
        }

        $result['ranking'] = $ranked;

        return $result;
    }
}

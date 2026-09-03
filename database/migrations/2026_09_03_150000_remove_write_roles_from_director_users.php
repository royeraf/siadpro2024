<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\User;
use Spatie\Permission\Models\Role;

/**
 * El rol Director debe ser exclusivamente visor de su propia institución
 * (permisos `*.director`) en los 8 módulos. Spatie suma los permisos de
 * TODOS los roles de un usuario, así que un usuario con Director + Docente
 * (o + PC) hereda además el CRUD completo de Docente/PC — "Mis registros",
 * crear, editar, eliminar — encima de su vista de solo lectura.
 *
 * 709 usuarios con cargo='Director' tenían esta combinación en producción
 * (708 con Docente además de Director, 1 con Docente+PC además de Director).
 * Se les revoca el rol de escritura y se conserva Director. Sus registros
 * históricos no se tocan y siguen siendo visibles desde la vista Director
 * (acotada por `users.institucion`, que incluye los propios).
 *
 * Se incluye a los inactivos para dejar la base consistente si se reactivan.
 * Deliberadamente NO se toca a los usuarios con cargo='Director' que no
 * tienen el rol Director asignado (su cargo puede estar desactualizado) ni a
 * quienes además tienen Admin o EspecUGEL (roles de administración/lectura,
 * no de escritura).
 *
 * Los ids están congelados en el momento de escribir esta migración para que
 * down() pueda restaurar exactamente lo que había, sin adivinar quién tenía
 * legítimamente esa combinación por otra vía (mismo criterio que
 * 2026_08_28_124146_assign_default_roles_to_roleless_active_users.php).
 *
 * Reemplaza a 2026_09_03_120000_remove_write_roles_from_director_users.php
 * (misma lista de ids, recalculada y verificada contra la BD): esa primera
 * migración se corrió, se revirtió a pedido y se borró; este es el mismo
 * arreglo re-aplicado.
 */
return new class extends Migration
{
    /**
     * Usuarios (cargo='Director', rol Director) a los que se les revoca
     * únicamente el rol Docente.
     */
    private array $docenteOnlyUserIds = [
        3, 8, 17, 18, 21, 29, 32, 34, 36, 48, 50, 51, 55, 56, 58, 60, 61, 67, 69, 71,
        74, 78, 83, 87, 89, 104, 105, 114, 118, 119, 120, 121, 125, 127, 128, 132, 138, 139, 140, 141,
        142, 143, 153, 157, 158, 160, 164, 169, 171, 175, 177, 180, 186, 188, 191, 192, 193, 194, 198, 200,
        202, 205, 206, 213, 214, 216, 217, 221, 228, 229, 234, 237, 240, 250, 251, 253, 255, 256, 258, 261,
        263, 264, 284, 285, 287, 288, 291, 297, 308, 309, 312, 314, 315, 321, 330, 332, 333, 334, 335, 336,
        338, 339, 340, 341, 343, 344, 345, 360, 384, 395, 398, 400, 401, 413, 415, 416, 428, 432, 436, 437,
        439, 440, 446, 447, 449, 452, 453, 457, 458, 459, 461, 462, 464, 465, 468, 470, 471, 472, 478, 479,
        481, 487, 488, 489, 490, 492, 493, 495, 497, 499, 502, 503, 504, 512, 513, 514, 515, 516, 521, 525,
        529, 532, 534, 535, 538, 540, 541, 543, 544, 547, 548, 554, 558, 559, 563, 571, 573, 575, 586, 587,
        588, 590, 591, 592, 594, 596, 603, 604, 609, 625, 626, 637, 651, 658, 660, 661, 664, 666, 668, 669,
        671, 674, 675, 676, 679, 686, 690, 692, 693, 696, 698, 702, 703, 706, 713, 718, 724, 728, 761, 769,
        780, 796, 803, 815, 830, 834, 845, 861, 882, 893, 894, 898, 902, 905, 914, 917, 931, 932, 945, 947,
        952, 953, 956, 957, 969, 971, 981, 985, 992, 1000, 1006, 1016, 1024, 1026, 1032, 1034, 1035, 1062, 1072, 1074,
        1095, 1106, 1107, 1109, 1110, 1111, 1112, 1113, 1114, 1115, 1116, 1117, 1118, 1119, 1120, 1122, 1123, 1124, 1125, 1127,
        1128, 1130, 1131, 1133, 1136, 1137, 1138, 1139, 1140, 1141, 1142, 1143, 1145, 1146, 1147, 1148, 1149, 1150, 1153, 1154,
        1156, 1158, 1159, 1172, 1174, 1178, 1181, 1182, 1183, 1191, 1192, 1193, 1195, 1198, 1200, 1207, 1208, 1209, 1210, 1211,
        1213, 1215, 1216, 1217, 1218, 1219, 1222, 1223, 1227, 1228, 1229, 1231, 1233, 1234, 1235, 1236, 1239, 1240, 1242, 1243,
        1246, 1247, 1249, 1258, 1268, 1283, 1292, 1296, 1297, 1300, 1318, 1335, 1382, 1395, 1402, 1419, 1423, 1427, 1428, 1429,
        1430, 1434, 1437, 1438, 1441, 1448, 1453, 1456, 1459, 1465, 1466, 1471, 1478, 1481, 1483, 1485, 1493, 1495, 1497, 1503,
        1505, 1507, 1509, 1530, 1536, 1544, 1562, 1566, 1567, 1579, 1582, 1586, 1592, 1596, 1598, 1613, 1616, 1619, 1620, 1625,
        1631, 1635, 1637, 1642, 1646, 1648, 1649, 1652, 1655, 1657, 1659, 1662, 1692, 1697, 1704, 1711, 1716, 1724, 1726, 1735,
        1740, 1759, 1762, 1765, 1769, 1774, 1789, 1793, 1800, 1807, 1810, 1811, 1817, 1834, 1837, 1840, 1844, 1850, 1851, 1855,
        1857, 1858, 1875, 1881, 1908, 1924, 1947, 1961, 1985, 1986, 1990, 1993, 1996, 1998, 1999, 2000, 2010, 2013, 2014, 2018,
        2019, 2021, 2023, 2024, 2027, 2034, 2035, 2036, 2044, 2045, 2046, 2047, 2049, 2052, 2054, 2055, 2060, 2065, 2068, 2070,
        2071, 2086, 2088, 2091, 2092, 2097, 2105, 2106, 2107, 2109, 2110, 2111, 2116, 2117, 2123, 2124, 2125, 2132, 2133, 2137,
        2143, 2146, 2148, 2150, 2152, 2154, 2156, 2163, 2164, 2167, 2170, 2172, 2175, 2180, 2181, 2186, 2198, 2200, 2202, 2204,
        2208, 2210, 2211, 2212, 2215, 2221, 2238, 2239, 2240, 2244, 2245, 2246, 2248, 2250, 2252, 2254, 2256, 2258, 2259, 2271,
        2305, 2331, 2334, 2335, 2337, 2340, 2341, 2343, 2345, 2347, 2354, 2368, 2372, 2387, 2390, 2396, 2408, 2419, 2432, 2436,
        2437, 2454, 2457, 2458, 2465, 2466, 2473, 2475, 2477, 2483, 2488, 2490, 2499, 2501, 2505, 2511, 2514, 2515, 2517, 2518,
        2523, 2524, 2533, 2534, 2560, 2580, 2581, 2582, 2584, 2592, 2593, 2628, 2665, 2668, 2689, 2699, 2701, 2709, 2743, 2748,
        2761, 2765, 2778, 2782, 2785, 2786, 2791, 2798, 2805, 2813, 2814, 2821, 2834, 2851, 2854, 2855, 2865, 2870, 2871, 2873,
        2874, 2875, 2900, 2932, 2933, 2934, 2937, 2941, 2970, 2976, 2978, 2979, 2991, 3056, 3057, 3080, 3087, 3090, 3119, 3130,
        3136, 3147, 3153, 3155, 3164, 3187, 3189, 3190, 3197, 3225, 3233, 3235, 3239, 3280, 3282, 3296, 3311, 3313, 3321, 3324,
        3344, 3346, 3364, 3365, 3366, 3401, 3448, 3450, 3455, 3467, 3478, 3490, 3495, 3500, 3514, 3539, 3549, 3583, 3668, 3746,
        3780, 3782, 3820, 3856, 4010, 4026, 4027, 4029, 4031, 4036, 4037, 4100, 4170, 4236, 4241, 4288, 4290, 4305, 4310, 4312,
        4346, 4354, 4359, 4373, 4395, 4396, 4398, 4448,
    ];

    /**
     * Único caso con una tercera combinación: Director + Docente + PC.
     */
    private array $docentePcUserIds = [3472];

    public function up()
    {
        $docente = Role::where('name', 'Docente')->first();
        $pc = Role::where('name', 'PC')->first();

        if (!$docente) {
            return;
        }

        User::whereIn('id', $this->docenteOnlyUserIds)
            ->get()
            ->each(fn (User $user) => $user->removeRole($docente));

        if ($pc) {
            User::whereIn('id', $this->docentePcUserIds)
                ->get()
                ->each(function (User $user) use ($docente, $pc) {
                    $user->removeRole($docente);
                    $user->removeRole($pc);
                });
        }
    }

    public function down()
    {
        $docente = Role::where('name', 'Docente')->first();
        $pc = Role::where('name', 'PC')->first();

        if (!$docente) {
            return;
        }

        User::whereIn('id', $this->docenteOnlyUserIds)
            ->get()
            ->each(fn (User $user) => $user->assignRole($docente));

        if ($pc) {
            User::whereIn('id', $this->docentePcUserIds)
                ->get()
                ->each(function (User $user) use ($docente, $pc) {
                    $user->assignRole($docente);
                    $user->assignRole($pc);
                });
        }
    }
};

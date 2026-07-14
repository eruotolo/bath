# Baños duplicados en obras activas — ajuste manual en producción

**Cliente:** Blanco Servicios e Inversiones SPA  
**Fecha:** 08-07-2026  
**Referencia:** hallazgo detectado al revisar el estado de los contratos (Fase 2 de las mejoras julio 2026). Este ajuste se probó y corrigió en el ambiente de testing, pero **no se aplicó en producción** — este informe es para que el equipo lo replique manualmente en el sistema real.

---

## Qué encontramos

**41 baños químicos** figuran asignados a más de una obra "Activa" al mismo tiempo. Son obras de un solo día que nunca se marcaron "Terminada" después del trabajo, y el baño se reutilizó después en otra obra sin cerrar la anterior.

## Paso 1 — Desasignar el baño de la obra vieja

Para cada fila: entrar a **Obra / Contratos → [obra vieja]**, buscar el baño en la sección "Baños de la Obra" y usar el botón de **desasignar** (flecha hacia abajo) solo para ese baño puntual. La obra vieja no se cierra en este paso, aunque pierda todos sus baños — eso se hace en el Paso 2 si corresponde.

| Baño | Obra vieja a desasignar (contrato) | Cliente | Pasa a esta obra (contrato) |
|---|---|---|---|
| **AT009** | #103 — Obra Coquiao | C.A.V. CONSTRUCCIONES | #164 — OBRA ACHAO |
| **AT010** | #103 — Obra Coquiao | C.A.V. CONSTRUCCIONES | #181 — OBRA MERCADO CHONCHI |
| **AT012** | #129 — OBRA PID PID | SONDAJES Y CONSTRUCCIONES PERFORROTER | #181 — OBRA MERCADO CHONCHI |
| **AT012** | #142 — SECTOR NAL - YUSTE FUERTE AHUI | CONSTRUCTORA ARIMAQ ANCUD SPA. | #181 — OBRA MERCADO CHONCHI |
| **AT013** | #103 — Obra Coquiao | C.A.V. CONSTRUCCIONES | #142 — SECTOR NAL - YUSTE FUERTE AHUI |
| **AT014** | #90 — OBRA CURACO DE VELEZ | INGENIERIA Y CONSTRUCCION 3M SOCIEDAD LIMITADA | #182 — OBRA RAMPLA CHACAO |
| **AT016** | #80 — Conservaciones Caminos Mechuque, Voigue y Cheniao | SOCIEDAD CONSTRUCTORA Y TRANSPORTES BREN-VAL | #210 — OBRA ANCUD - DEGAN |
| **AT016** | #143 — SECTOR ALTO MURO | MINGA CONSTRUCCIONES SPA | #210 — OBRA ANCUD - DEGAN |
| **AT023** | #54 — OBRA GLOBAL CHONCHI - QUELLON (OC 36/2023) | INGENIERIA Y CONSTRUCTORA DINAMARCA Y JARA LIMITADA | #187 — OBRA CASTRO |
| **AT025** | #54 — OBRA GLOBAL CHONCHI - QUELLON (OC 36/2023) | INGENIERIA Y CONSTRUCTORA DINAMARCA Y JARA LIMITADA | #186 — OBRA CASTRO |
| **AT026** | #86 — BAPER AUCHAC | EMPRESA CONSTRUCTORA BAPER S.A | #155 — CURACO DE VELEZ |
| **AT026** | #109 — MOWI -RAUCO | SOC. GPM INGENIERÍA Y CONSTYRUCCION LTDA | #155 — CURACO DE VELEZ |
| **AT026** | #123 — OBRA NOTUCO | CONSTRUCTORA ANTONIO AVILA OLEA LTDA | #155 — CURACO DE VELEZ |
| **AT027** | #18 — OBRA BASICO PUQUELDON 2023 | CONSTRUCTORA PUERTO OCTAY LTDA | #141 — SECTOR PETANES |
| **AT027** | #124 — OC 02/29042025 | CULTIVOS CURAHUE S.A. | #141 — SECTOR PETANES |
| **AT028** | #137 — CAMINO PUTIQUE - COÑAB, ACHAO | INGENIERIA Y CONSTRUCCION EL NARANJO SPA | #146 — OBRA ACHAO - CAMINO HUYAR - DIAÑ |
| **AT030** | #196 — SECTOR PILLUL ALTO | COMERCIALIZADORA Y CONSTRUCTORA LIHUEN SPA | #201 — SECTOR  TARAHUIN |
| **AT031** | #143 — SECTOR ALTO MURO | MINGA CONSTRUCCIONES SPA | #182 — OBRA RAMPLA CHACAO |
| **AT035** | #69 — Faenas Provincia de Chiloe | MOP - DIRECCION DE VIALIDAD | #81 — OBRA SECTOR ROMAZAL |
| **AT036** | #79 — OBRA COSTANERA | BIMAC INGENIERIA Y CONSTRUCCIÓN SpA | #173 — SECTOR COINCO |
| **AT040** | #75 — PULPITO | JOSE ARTURO OYARZUN TORRES | #85 — PASAJE DALCAHUE |
| **AT041** | #141 — SECTOR PETANES | C.A.V. CONSTRUCCIONES | #172 — QUEMCHI |
| **AT044** | #102 — OBRA NOTUCO | BIMAC INGENIERIA Y CONSTRUCCIÓN SpA | #144 — SECTOR QUICAVI - QUEMCHI |
| **AT046** | #61 — Obra Bomberos - Albergue | VALCA CONSTRUCCIONES SPA | #141 — SECTOR PETANES |
| **AT049** | #56 — OBRA ESTADIO CURACO DE VELEZ (OC 005-190224) | JUAN JOSE SILES CARVAJAL | #210 — OBRA ANCUD - DEGAN |
| **AT049** | #177 — OBRA CASTRO | CONSTRUCTORA ARANCIBIA SPA | #210 — OBRA ANCUD - DEGAN |
| **AT050** | #98 — HOSPITAL ACHAO | JOSE ARTURO OYARZUN TORRES | #164 — OBRA ACHAO |
| **AT060** | #81 — OBRA SECTOR ROMAZAL | INGENIERIA Y CONSTRUCTORA DINAMARCA Y JARA LIMITADA | #163 — SECTOR LLAU LLAO-CASTRO |
| **AT060** | #131 — OC RAM 045 | INGENIERIA Y CONSTRUCCION MUÑOZ Y SALAZAR LTDA. | #163 — SECTOR LLAU LLAO-CASTRO |
| **AT060** | #133 — OC RAM045 | INGENIERIA Y CONSTRUCCION MUÑOZ Y SALAZAR LTDA. | #163 — SECTOR LLAU LLAO-CASTRO |
| **AT062** | #152 — RUTA 5 - QUELLON | INGENIERIA Y CONSTRUCCION PRC S.A. | #154 — MULTICANCHA CALLE INES DE BAZAN - CASTRO |
| **AT063** | #69 — Faenas Provincia de Chiloe | MOP - DIRECCION DE VIALIDAD | #127 — CHACAO |
| **AT064** | #56 — OBRA ESTADIO CURACO DE VELEZ (OC 005-190224) | JUAN JOSE SILES CARVAJAL | #197 — OBRA CAMINO A CHONCHI- QUELLON |
| **AT066** | #61 — Obra Bomberos - Albergue | VALCA CONSTRUCCIONES SPA | #140 — SECTOR RILAN- AGUANTAO |
| **AT066** | #124 — OC 02/29042025 | CULTIVOS CURAHUE S.A. | #140 — SECTOR RILAN- AGUANTAO |
| **AT067** | #77 — Dalcahue (OC8728) | CORDILLERA INGENIERIA Y CONSTRUCCION SPA | #184 — SECTOR COGOMO |
| **AT067** | #156 — CONSTRUCCION AREA VERDE ALTOS DE CHILOE | ASESORIA Y CONSTRUCCION ENTREVIGAS SPA | #184 — SECTOR COGOMO |
| **AT070** | #74 — PAV HOSPITAL ANCUD | CONSTRUCTORA CAMPODONICO Y CIA LTDA | #172 — QUEMCHI |
| **AT070** | #165 — SECTOR CURAHUE | CULTIVOS CURAHUE S.A. | #172 — QUEMCHI |
| **AT071** | #159 — OBRA DALCAHUE | BIMAC INGENIERIA Y CONSTRUCCIÓN SpA | #180 — OBRA HOSPITAL DE ANCUD |
| **AT071** | #166 — OBRA DALCAHUE | BIMAC INGENIERIA Y CONSTRUCCIÓN SpA | #180 — OBRA HOSPITAL DE ANCUD |
| **AT074** | #81 — OBRA SECTOR ROMAZAL | INGENIERIA Y CONSTRUCTORA DINAMARCA Y JARA LIMITADA | #130 — OC 901160 |
| **AT075** | #160 — SECTOR QUILO - ANCUD | CHILOE GRAPPLING | #208 — OBRA DALCAHUE |
| **AT077** | #78 — Baño sector Chulchuy | SALMONES AYSÉN S.A. | #160 — SECTOR QUILO - ANCUD |
| **AT077** | #152 — RUTA 5 - QUELLON | INGENIERIA Y CONSTRUCCION PRC S.A. | #160 — SECTOR QUILO - ANCUD |
| **AT082** | #86 — BAPER AUCHAC | EMPRESA CONSTRUCTORA BAPER S.A | #178 — OBRA ACHAO |
| **AT082** | #116 — OBRA TEY | NORDVATTEN DESARROLLOS SPA. | #178 — OBRA ACHAO |
| **AT087** | #66 — TERMINACION OBRAS CURACO DE VELEZ | INVERSIONES SURCON SPA | #137 — CAMINO PUTIQUE - COÑAB, ACHAO |
| **AT087** | #124 — OC 02/29042025 | CULTIVOS CURAHUE S.A. | #137 — CAMINO PUTIQUE - COÑAB, ACHAO |
| **AT088** | #18 — OBRA BASICO PUQUELDON 2023 | CONSTRUCTORA PUERTO OCTAY LTDA | #125 — Contrato: Quilar - Doca |
| **AT091** | #69 — Faenas Provincia de Chiloe | MOP - DIRECCION DE VIALIDAD | #104 — LINAO-ANCUD |
| **AT092** | #74 — PAV HOSPITAL ANCUD | CONSTRUCTORA CAMPODONICO Y CIA LTDA | #163 — SECTOR LLAU LLAO-CASTRO |
| **AT095** | #103 — Obra Coquiao | C.A.V. CONSTRUCCIONES | #173 — SECTOR COINCO |
| **AT095** | #106 — VILLA GUARELO | AGONI CONSTRUCCIONES LTDA | #173 — SECTOR COINCO |
| **AT098** | #129 — OBRA PID PID | SONDAJES Y CONSTRUCCIONES PERFORROTER | #153 — SECTOR CURBITA |
| **CARRO** | #95 — OBRA CASTRO | CONSTRUCTORA MICHILOE | #207 — CENTRO CIVICO DALCAHUE |
| **FOSA-SEPTICA** | #60 — segun oc 33486 | Corporación Municipal de Castro | #112 — VILLA BORDEMAR |
| **FOSA-SEPTICA** | #68 — Instalacion Faena - Limpieza Fosas | Inversiones Tranqui SpA | #112 — VILLA BORDEMAR |
| **FOSA-SEPTICA** | #72 — Fosa Séptica Dalca Express | RAUL MANSILLA MUÑOZ | #112 — VILLA BORDEMAR |

## Paso 2 — Cerrar las obras que quedan sin ningún baño

De las obras de la tabla de arriba, estas **32** se quedan sin ningún baño asignado después del Paso 1 (no tienen otro baño legítimo, a diferencia del resto que sigue con otros baños en uso). Hay que marcarlas manualmente como **"Terminado"** (botón de candado "Inactivar").

**Importante:** no usar el botón "Inactivar" en NINGUNA otra obra de la tabla del Paso 1 que no esté en esta lista — ese botón borra TODOS los baños de la obra de una vez, y esas obras siguen usando otros baños legítimamente.

| Contrato | Obra |
|---|---|
| #18 | OBRA BASICO PUQUELDON 2023 |
| #54 | OBRA GLOBAL CHONCHI - QUELLON (OC 36/2023) |
| #56 | OBRA ESTADIO CURACO DE VELEZ (OC 005-190224) |
| #60 | segun oc 33486 |
| #66 | TERMINACION OBRAS CURACO DE VELEZ |
| #68 | Instalacion Faena - Limpieza Fosas |
| #72 | Fosa Séptica Dalca Express |
| #74 | PAV HOSPITAL ANCUD |
| #77 | Dalcahue (OC8728) |
| #78 | Baño sector Chulchuy |
| #79 | OBRA COSTANERA |
| #80 | Conservaciones Caminos Mechuque, Voigue y Cheniao |
| #86 | BAPER AUCHAC |
| #90 | OBRA CURACO DE VELEZ |
| #95 | OBRA CASTRO |
| #98 | HOSPITAL ACHAO |
| #102 | OBRA NOTUCO |
| #106 | VILLA GUARELO |
| #109 | MOWI -RAUCO |
| #116 | OBRA TEY |
| #123 | OBRA NOTUCO |
| #124 | OC 02/29042025 |
| #129 | OBRA PID PID |
| #131 | OC RAM 045 |
| #133 | OC RAM045 |
| #143 | SECTOR ALTO MURO |
| #156 | CONSTRUCCION AREA VERDE ALTOS DE CHILOE |
| #159 | OBRA DALCAHUE |
| #165 | SECTOR CURAHUE |
| #166 | OBRA DALCAHUE |
| #177 | OBRA CASTRO |
| #196 | SECTOR PILLUL ALTO |
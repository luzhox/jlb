# Formulario Open Day → HubSpot

El módulo `jlb_open_day_form` (Figma 4172:416) envía sus registros a HubSpot
mediante la **Forms Submission API**, a través de un **proxy server-side** en
WordPress. El navegador NUNCA habla directo con HubSpot: así mantenemos el token
fuera del cliente, validamos nonce/honeypot/reCAPTCHA y sanitizamos en el servidor.

## Flujo

```
<form data-jlb-openday>  ──fetch POST──►  /wp-json/jlb/v1/open-day  ──wp_remote_post──►  api.hsforms.com
   (src/jlbOpenDayForm.js)                 (inc/hubspot.php)                              (HubSpot)
```

- **Front:** `src/jlbOpenDayForm.js` valida en cliente (requeridos, email,
  consentimientos), envía `X-WP-Nonce` + campos y muestra estado accesible.
- **Servidor:** `inc/hubspot.php` → `jlb_open_day_submit()`:
  1. Verifica nonce `wp_rest` (CSRF).
  2. Descarta spam por honeypot (`website`).
  3. Sanitiza (`sanitize_text_field`, `sanitize_email`).
  4. Valida requeridos + email + consentimientos (422 con `errors` por campo).
  5. Verifica reCAPTCHA si hay secret.
  6. `jlb_hubspot_send()` arma el payload y hace `wp_remote_post`.

## Endpoints HubSpot usados

- **Público** (por defecto, sin token):
  `POST https://api.hsforms.com/submissions/v3/integration/submit/{portalId}/{formGuid}`
- **Seguro** (si se define `JLB_HUBSPOT_TOKEN`, private app):
  `POST .../integration/secure/submit/{portalId}/{formGuid}` con `Authorization: Bearer`.

Payload (v3): `{ submittedAt, fields:[{objectTypeId:"0-1", name, value}], context:{pageUri,pageName,ipAddress,hutk}, legalConsentOptions }`.

> Regla de HubSpot: **solo se envían campos que existan en el formulario** de
> HubSpot. Si agregas/quitas campos, ajusta el form en HubSpot y el mapeo abajo.

## Configuración

Hay **dos vías**; el valor se resuelve con `jlb_hubspot_cfg($key)` y la
**constante en wp-config tiene prioridad** sobre el panel.

### A) Administrable desde wp-admin (recomendado para no-secretos)

**Ajustes del sitio › Integraciones — Open Day / HubSpot › pestaña HubSpot**:

- **HubSpot Portal ID (Hub ID)** → opción `jlb_hs_portal_id`
- **HubSpot Form GUID** → opción `jlb_hs_form_guid`
- **reCAPTCHA v2 — Site key (público)** → opción `jlb_hs_recaptcha_site`

Estos tres **no son secretos** (aparecen en el embed público de HubSpot / en el
HTML del front), por eso son editables desde el admin.

### B) Constantes en wp-config.php (obligatorio para SECRETOS)

```php
// Override de los campos del panel (opcional para portal/guid):
define('JLB_HUBSPOT_PORTAL_ID', '12345678');
define('JLB_HUBSPOT_FORM_GUID', 'xxxxxxxx-xxxx-xxxx-...');
define('JLB_RECAPTCHA_SITE_KEY', '6Lc...');
// SECRETOS — solo aquí (nunca se leen de la BD):
define('JLB_HUBSPOT_TOKEN', 'pat-na1-...');   // token private app → endpoint /secure
define('JLB_RECAPTCHA_SECRET',  '6Lc...');     // verificación server-side reCAPTCHA
```

> El **token** y el **reCAPTCHA secret** son secretos: `jlb_hubspot_cfg()` los lee
> **solo de constante**, nunca de `wp_options`, para no exponerlos en la BD.

Sin Portal ID / Form GUID (ni en panel ni en constante) el endpoint **no falla**:
dispara la acción `jlb_openday_lead` (captura alternativa) y responde `ok` con
`hubspot.status = "skipped"`. Útil en local/staging.

## Mapeo de campos (filtrable)

El nombre `name` de cada `field` debe coincidir con la **propiedad interna** del
form en HubSpot. Por defecto:

| Campo del form | Propiedad HubSpot |
|---|---|
| `correo`        | `email`             |
| `responsable`   | `firstname`         |
| `celular`       | `phone`             |
| `postulante`    | `nombre_postulante` |
| `grado`         | `grado_postula`     |
| `anio_admision` | `anio_admision`     |
| `hora_visita`   | `hora_visita`       |

Ajústalo sin tocar el core:

```php
add_filter('jlb_hubspot_field_map', function ($map, $data) {
    $map['postulante'] = 'nombre_del_postulante'; // tu propiedad real
    return $map;
}, 10, 2);
```

Las propiedades personalizadas (`nombre_postulante`, `grado_postula`,
`anio_admision`, `hora_visita`) deben **existir como propiedades de contacto** y
estar **añadidas al formulario** en HubSpot, o la API las rechaza.

## Consentimiento legal (GDPR)

`legalConsentOptions` se arma por defecto con `consentToProcess` + texto, y es
filtrable (las `communications` requieren `subscriptionTypeId`, propio de cada
portal):

```php
add_filter('jlb_hubspot_legal', function ($legal, $data) {
    $legal['consent']['communications'] = array(array(
        'value' => $data['consent_info'],
        'subscriptionTypeId' => 999999,            // tu Subscription Type ID
        'text' => 'Acepto recibir información del colegio.',
    ));
    return $legal;
}, 10, 2);
```

## Captura alternativa (sin HubSpot o además de él)

```php
add_action('jlb_openday_lead', function ($data, $request) {
    wp_mail(get_option('admin_email'), 'Nuevo registro Open Day', print_r($data, true));
}, 10, 2);
```

## Notas de seguridad

- CSRF: nonce `wp_rest` obligatorio.
- Anti-spam: honeypot + reCAPTCHA opcional.
- Todo input sanitizado; nada se imprime de vuelta (sin XSS reflejado).
- El token de HubSpot vive solo en el servidor (endpoint `/secure`).
- Rate limit de HubSpot (público): 50 req/10s — el proxy hereda ese límite.

#!/usr/bin/env bash
#
# Revisa la configuración de correo de un dominio: MX, SPF, DKIM, DMARC.
#
#   ./scripts/dns-correo.sh yjscompany.com
#
# Usa DNS-over-HTTPS (dns.google) en lugar de `dig` a propósito: evita el
# resolutor local, la caché del sistema y los entornos que interceptan el
# puerto 53. Solo necesita curl y python3.
#
set -uo pipefail

DOMAIN="${1:-}"
if [[ -z "$DOMAIN" ]]; then
    echo "uso: $0 <dominio>" >&2
    exit 1
fi

# Selectores DKIM de los proveedores más habituales. DKIM no se puede
# enumerar: hay que adivinar el selector, así que se prueban los conocidos.
# Si usáis un proveedor que no esté aquí, añadidlo a la lista.
SELECTORES=(
    default mail dkim smtp
    google                       # Google Workspace
    selector1 selector2          # Microsoft 365
    zoho zohomail                # Zoho
    s1 s2 k1 k2 k3               # SendGrid, Mailchimp/Mandrill
    mandrill sendgrid mailjet mailgun em
    pm protonmail1 protonmail2   # Proton
    titan hostinger cpanel       # hosting compartido
    dk sig1 mte1
)

consulta() {   # consulta <nombre> <tipo> → una línea por registro
    curl -s --max-time 10 "https://dns.google/resolve?name=$1&type=$2" \
        | python3 -c "
import sys, json
try:
    d = json.load(sys.stdin)
except Exception:
    sys.exit(0)
for a in d.get('Answer') or []:
    print(a.get('data', ''))
"
}

titulo() { printf '\n\033[1m%s\033[0m\n' "$1"; }
ok()     { printf '  \033[32m✔\033[0m %s\n' "$1"; }
falta()  { printf '  \033[31m✘\033[0m %s\n' "$1"; }
info()   { printf '    %s\n' "$1"; }

echo "════════════════════════════════════════════════════"
echo " Diagnóstico de correo · $DOMAIN"
echo " $(date '+%Y-%m-%d %H:%M')"
echo "════════════════════════════════════════════════════"

titulo "Delegación (nameservers)"
NS=$(consulta "$DOMAIN" NS)
if [[ -n "$NS" ]]; then
    while read -r r; do ok "$r"; done <<< "$NS"
else
    falta "sin NS en la zona"
    info "el registrador puede tenerlos aunque la zona esté vacía; comprueba el WHOIS"
fi

titulo "MX · quién recibe el correo"
MX=$(consulta "$DOMAIN" MX)
if [[ -n "$MX" ]]; then
    while read -r r; do ok "$r"; done <<< "$MX"
else
    falta "SIN REGISTROS MX — el dominio no puede recibir correo"
fi

titulo "SPF · quién puede enviar en su nombre"
SPF=$(consulta "$DOMAIN" TXT | grep -i "v=spf1" || true)
if [[ -n "$SPF" ]]; then
    while read -r r; do ok "$r"; done <<< "$SPF"
else
    falta "SIN SPF — lo que se envíe acabará en spam o será rechazado"
fi

titulo "Otros TXT"
TXT=$(consulta "$DOMAIN" TXT | grep -iv "v=spf1" || true)
if [[ -n "$TXT" ]]; then
    while read -r r; do info "$r"; done <<< "$TXT"
else
    info "(ninguno)"
fi

titulo "DKIM · firma de los envíos"
ENCONTRADO=0
for s in "${SELECTORES[@]}"; do
    R=$(consulta "${s}._domainkey.${DOMAIN}" TXT)
    if [[ -n "$R" ]]; then
        ok "selector '${s}'"
        info "$(echo "$R" | cut -c1-100)…"
        ENCONTRADO=1
    fi
done
if [[ $ENCONTRADO -eq 0 ]]; then
    falta "ningún selector conocido responde"
    info "ojo: DKIM no se puede enumerar. Si usáis un selector propio,"
    info "añadidlo al array SELECTORES de este script."
fi

titulo "DMARC · qué hacer con lo que no autentica"
DMARC=$(consulta "_dmarc.${DOMAIN}" TXT)
if [[ -n "$DMARC" ]]; then
    while read -r r; do ok "$r"; done <<< "$DMARC"
else
    falta "sin DMARC"
fi

titulo "Registro del dominio"
# ${VAR^^} necesita bash 4 y macOS trae el 3.2, así que se pasa por tr.
DOMAIN_UP=$(echo "$DOMAIN" | tr '[:lower:]' '[:upper:]')
curl -s --max-time 20 "https://rdap.verisign.com/com/v1/domain/${DOMAIN_UP}" \
    | python3 -c "
import sys, json
try:
    d = json.load(sys.stdin)
except Exception:
    print('    (no se pudo consultar el RDAP)'); sys.exit(0)
print('    estado:', ', '.join(d.get('status', [])) or '—')
for e in d.get('events', []):
    print(f\"    {e.get('eventAction')}: {e.get('eventDate', '')[:10]}\")
ns = [n.get('ldhName','').lower() for n in d.get('nameservers', [])]
if ns:
    print('    nameservers en el registrador:')
    for n in ns: print('      -', n)
" 2>/dev/null

echo ""
echo "════════════════════════════════════════════════════"

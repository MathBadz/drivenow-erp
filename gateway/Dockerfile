# DriveNow ERP — API Gateway (single public entry point).
# Thin nginx reverse proxy; routes by Host to the owning service container.
FROM nginx:1.27-alpine

# Container routing config (Docker-service upstreams), replaces the default site.
COPY nginx.docker.conf /etc/nginx/conf.d/default.conf
COPY _proxy.inc        /etc/nginx/conf.d/_proxy.inc

HEALTHCHECK --interval=30s --timeout=5s --retries=5 \
    CMD wget -qO- http://127.0.0.1/healthz >/dev/null 2>&1 || exit 1

EXPOSE 80

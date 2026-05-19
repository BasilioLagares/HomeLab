#!/usr/bin/env bash

echo "=== Proyecto HomeLab - Estado general ==="
echo

echo "=== Docker ==="
docker --version
docker compose version
echo

echo "=== Contenedores activos ==="
docker ps --format "table {{.Names}}\t{{.Image}}\t{{.Status}}\t{{.Ports}}"
echo

echo "=== Volúmenes Docker ==="
docker volume ls | grep fase-1-mvp || true
echo

echo "=== Uso de espacio Docker ==="
docker system df

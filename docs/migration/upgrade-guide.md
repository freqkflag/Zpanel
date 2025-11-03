# Upgrade Guide

Guide for upgrading between Zpanel versions and migrating from Coolify.

## Upgrading Zpanel

### Before Upgrading

1. **Backup your data**
   ```bash
   # Database backup
   docker exec zpanel-db pg_dump -U zpanel > backup-$(date +%Y%m%d).sql
   
   # Configuration backup
   cp .env .env.backup
   
   # Application data backup
   tar czf zpanel-data-backup.tar.gz implementation/phase-1/Zpanel/storage
   ```

2. **Review changelog**
   - Read [CHANGELOG-ZPANEL.md](../../CHANGELOG-ZPANEL.md)
   - Note breaking changes
   - Check migration requirements

3. **Test in staging**
   - Never upgrade production directly
   - Test upgrade in staging environment first
   - Verify all features work

### Upgrade Process

#### Minor Version Upgrades (0.1.0 → 0.1.1)

```bash
# Navigate to project
cd /opt/zpanel

# Pull latest changes
git fetch origin
git checkout v0.1.1

# Update dependencies
cd implementation/phase-1/Zpanel
composer install --no-dev
npm install
npm run build

# Run migrations
php artisan migrate --force

# Clear caches
php artisan optimize:clear
php artisan optimize

# Restart services
docker-compose restart
```

#### Major Version Upgrades (0.x.0 → 1.0.0)

**Read upgrade guide for specific version:**

Major upgrades may require:
- Database schema changes
- Configuration updates
- Code migrations
- Service restarts

Follow version-specific upgrade guide in `docs/migration/`.

### Post-Upgrade

1. **Verify services**
   ```bash
   docker-compose ps
   ```

2. **Check logs**
   ```bash
   docker-compose logs -f app
   tail -f implementation/phase-1/Zpanel/storage/logs/laravel.log
   ```

3. **Test critical features**
   - Log in to dashboard
   - Deploy test application
   - Check API endpoints
   - Verify integrations work

4. **Monitor for issues**
   - Watch logs for errors
   - Check error tracking (Sentry)
   - Monitor resource usage

## Migrating from Coolify

If you're running Coolify and want to migrate to Zpanel:

### Assessment

**Compatibility:**
- Zpanel is based on Coolify v4.0.0-beta.437
- Most Coolify features will work
- Zpanel adds additional features

**Migration Path:**
- In-place upgrade (risky)
- Parallel installation (recommended)

### Parallel Migration (Recommended)

1. **Install Zpanel** on new server
   ```bash
   # On new server
   curl -fsSL https://install.zpanel.dev/install.sh | bash
   ```

2. **Export Coolify data**
   ```bash
   # On Coolify server
   docker exec coolify-db pg_dump -U coolify > coolify-export.sql
   
   # Export configurations
   # Via Coolify UI: Settings → Backup → Export
   ```

3. **Transfer data** to new server
   ```bash
   scp coolify-export.sql user@zpanel-server:/tmp/
   scp coolify-backup.json user@zpanel-server:/tmp/
   ```

4. **Import to Zpanel**
   ```bash
   # On Zpanel server
   docker exec zpanel-db psql -U zpanel < /tmp/coolify-export.sql
   
   # Import configurations via UI
   # Zpanel → Settings → Restore → Import
   ```

5. **Migrate applications** one by one
   - Redeploy applications on Zpanel
   - Test thoroughly
   - Update DNS when confirmed working
   - Decommission Coolify after all migrations

### In-Place Upgrade (Advanced)

**⚠️ Warning**: High risk, backup everything first!

```bash
# On Coolify server
cd /opt/coolify

# Backup
tar czf coolify-backup-$(date +%Y%m%d).tar.gz .

# Add Zpanel remote
git remote add zpanel https://github.com/freqkflag/Zpanel.git

# Fetch Zpanel
git fetch zpanel

# Merge (may have conflicts)
git merge zpanel/main

# Resolve conflicts
# Update dependencies
composer install
npm install && npm run build

# Run migrations
php artisan migrate

# Restart
docker-compose restart
```

## Version-Specific Upgrades

### 0.1.0-alpha → 0.2.0

**Breaking changes:**
- None yet (alpha version)

**New features:**
- IDE integration
- Enhanced MCP framework
- Cloudflare automation

**Migration steps:**
```bash
# Standard upgrade process
git pull origin main
composer install
npm install && npm run build
php artisan migrate
```

## Rollback Procedures

### Quick Rollback

If upgrade fails:

```bash
# Stop services
docker-compose down

# Restore from backup
docker exec -i zpanel-db psql -U zpanel < backup.sql
cp .env.backup .env

# Checkout previous version
git checkout v0.1.0

# Restart
docker-compose up -d
```

### Data Recovery

If database corrupted:

```bash
# Restore from latest backup
docker exec -i zpanel-db psql -U zpanel < /backups/latest.sql

# Or from S3
aws s3 cp s3://zpanel-backups/latest.sql - | docker exec -i zpanel-db psql -U zpanel
```

## Compatibility Matrix

### Zpanel Versions

| Zpanel | Coolify Base | PHP | Laravel | Node |
|--------|--------------|-----|---------|------|
| 0.1.0  | 4.0.0-beta.437 | 8.4 | 12.20   | 20   |
| 0.2.0  | TBD          | 8.4 | 12.x    | 20   |

### Database Compatibility

| Zpanel | PostgreSQL | Redis |
|--------|------------|-------|
| 0.1.0  | 15+        | 7+    |
| 0.2.0  | 15+        | 7+    |

## Troubleshooting Upgrades

### Migration Failures

**Issue**: `php artisan migrate` fails

**Solutions:**
```bash
# Check migration status
php artisan migrate:status

# Try with --force
php artisan migrate --force

# If still fails, check logs
tail -f storage/logs/laravel.log
```

### Dependency Conflicts

**Issue**: Composer dependency conflicts

**Solutions:**
```bash
# Clear composer cache
composer clear-cache

# Update dependencies
composer update

# If conflicts persist, check composer.json for version constraints
```

### Asset Build Failures

**Issue**: `npm run build` fails

**Solutions:**
```bash
# Clear npm cache
rm -rf node_modules package-lock.json
npm cache clean --force

# Reinstall
npm install
npm run build
```

## Best Practices

### Upgrade Planning

- ✅ **DO** read changelog before upgrading
- ✅ **DO** backup before upgrading
- ✅ **DO** test in staging first
- ✅ **DO** schedule during low-traffic periods
- ❌ **DON'T** skip versions
- ❌ **DON'T** upgrade during peak hours
- ❌ **DON'T** skip backups

### Maintenance Window

Recommended upgrade schedule:
- **Minor updates**: Weekly or bi-weekly
- **Major updates**: Monthly or quarterly
- **Security updates**: Immediately
- **Test in staging**: Always

## Additional Resources

- [Installation Guide](../guides/installation.md)
- [Changelog](../../CHANGELOG-ZPANEL.md)
- [Backup & Recovery](backup-recovery.md) (when available)
- [Troubleshooting](troubleshooting.md) (when available)

## Support

Need help with upgrades?

- **Documentation**: Check this guide first
- **GitHub Issues**: Search for similar issues
- **Discord**: #support channel
- **Professional Support**: Contact for enterprise support options


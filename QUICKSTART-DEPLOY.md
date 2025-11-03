# Zpanel Quick Start Guide

Get Zpanel running in 5 minutes with Docker!

---

## 🚀 One-Line Installation

```bash
curl -fsSL https://raw.githubusercontent.com/freqkflag/Zpanel/zpanel/implementation/phase-1/Zpanel/deploy.sh | bash
```

**That's it!** Access Zpanel at `http://your-server` when complete.

---

## 📦 Manual Installation (Recommended)

### 1. Download Zpanel

```bash
git clone https://github.com/freqkflag/Zpanel.git
cd Zpanel/implementation/phase-1/Zpanel
```

### 2. Configure

```bash
# Create environment file
cp .env.production.template .env

# Edit with your settings
nano .env
```

**Minimum required settings:**
```env
APP_URL=http://your-domain.com
DB_PASSWORD=YourSecurePassword123
CODE_SERVER_PASSWORD=YourIDEPassword456
KONG_DB_PASSWORD=YourKongPassword789
```

### 3. Deploy

```bash
chmod +x deploy.sh
./deploy.sh
```

### 4. Access

Open your browser to:
```
http://your-server
```

Default login will be created on first run.

---

## 🎯 What Gets Installed

✅ **Zpanel Control Panel** - Main application  
✅ **PostgreSQL 15** - Database  
✅ **Redis 7** - Cache & Queues  
✅ **code-server** - Integrated IDE  
✅ **Kong Gateway** - API Management  
✅ **Soketi** - WebSocket server  

**Total Containers**: 7  
**Disk Space**: ~2GB  
**Memory**: ~1GB minimum, 2GB recommended  

---

## 🔧 Post-Installation

### Create Admin User

```bash
docker-compose -f docker-compose.prod.yml exec zpanel php artisan make:admin
```

### Access Integrated IDE

1. Log in to Zpanel
2. Navigate to **IDE** in the menu
3. Your workspace loads automatically
4. Start coding!

### Configure MCP Servers

1. Go to **MCP Servers** in menu
2. Click **+ Add Server**
3. Choose server type (Cloudflare, GitHub, etc.)
4. Configure and save

### Set Up API Gateway

1. Navigate to **API Gateway**
2. Click **+ Add Service**
3. Configure your backend service
4. Add routes and rate limiting

---

## 🐛 Troubleshooting

### Services won't start?

```bash
# Check what's running
docker-compose -f docker-compose.prod.yml ps

# View logs
docker-compose -f docker-compose.prod.yml logs -f
```

### Can't access the web interface?

```bash
# Check if port 80 is available
sudo netstat -tlnp | grep :80

# Try different port
APP_PORT=8080 ./deploy.sh
```

### Database errors?

```bash
# Reset database
docker-compose -f docker-compose.prod.yml down -v
./deploy.sh
```

---

## 📚 Next Steps

1. **Secure Your Installation**
   - Set up SSL/TLS
   - Configure firewall
   - Enable 2FA

2. **Explore Features**
   - Try the integrated IDE
   - Create an MCP server
   - Set up API Gateway

3. **Read Full Documentation**
   - [Deployment Guide](DEPLOYMENT.md)
   - [Architecture Diagrams](../../docs/diagrams/)
   - [API Documentation](docs/api/)

---

## 🆘 Get Help

- **Issues**: https://github.com/freqkflag/Zpanel/issues
- **Discussions**: https://github.com/freqkflag/Zpanel/discussions
- **Documentation**: [Full Docs](../../docs/)

---

## ⭐ Features

- 🤖 **AI Integration** via MCP servers
- 💻 **Integrated IDE** (code-server)
- 🔌 **API Gateway** (Kong)
- ☁️ **Cloudflare Integration**
- 🚀 **Application Deployment**
- 🗄️ **Database Management**
- 📊 **Real-time Monitoring**

**Based on**: [Coolify](https://github.com/coollabsio/coolify) (Apache-2.0)

---

**Happy Deploying! 🎉**


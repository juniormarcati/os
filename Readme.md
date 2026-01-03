# 🚀 PluginOS

Gere facilmente **Ordens de Serviço (OS)** a partir dos dados de tickets no GLPI.  
Este plugin foi desenvolvido para facilitar a emissão de documentos de serviços técnicos, com layouts em **A4 e etiqueta**.

**Languages:** 🇺🇸 [English](#english) | 🇧🇷 [Português (BR)](#portugues-br) | 🇪🇸 [Español](#espanol) | 🇫🇷 [Français](#francais)

---

## 📚 Table of Contents
- 📦 [Installation](#-installation)
- ⚙️ [Configuration](#-configuration)
- 🛠️ [Usage](#-usage)
- 📁 [Available Layouts](#-available-layouts)
- 🌎 [Translations](#-translations)
- 📜 [License](#-license)

---

## 📦 Installation
1. Clone this repository:
```bash
git clone https://github.com/juniormarcati/os/os.git
```

2. Copy the project to:
`glpi/plugins/`

3. Ensure the final folder name is `os`.

4. In GLPI, go to:  
**Setup > Plugins > Install > Enable**

---

## ⚙️ Configuration

- After enabling, a **“Service Order”** submenu appears under **Plugins**.  
- Fill in the required fields; they form the document header.

- Go to: **Administration > Entities**  
- Open the **“OS Data”** tab  
- Fill in the company ID for each entity.

---

## 🛠️ Usage

1. Open any ticket.  
2. A new **“Service Order”** tab appears automatically.  
3. Choose the desired layout:
- **A4** — ideal for standard printing.  
- **Label** — compact version for thermal/label printers.

---

## 📁 Available Layouts

| Layout | Description | Best for |
|--------|-------------|----------|
| **A4** | Full document | Standard printers |
| **Label** | Compact version | Label/thermal printers |

---

## 🌎 Translations
Prefer your language? Jump to: 🇺🇸 [English](#english) | 🇧🇷 [Português (BR)](#portugues-br) | 🇪🇸 [Español](#espanol) | 🇫🇷 [Français](#francais)

### <a id="portugues-br"></a> 🇧🇷 Português (PT_BR)

Este plugin gera **Ordem de Serviço (OS)** com base nos dados do ticket no GLPI.

Instalação:
- Faça o download/clone e extraia em `glpi/plugins/`.
- Renomeie a pasta para `os`, se necessário.
- No GLPI: **Configurar > Plug-ins > Instalar > Habilitar**.

Configuração:
- Após habilitar, aparece o submenu **“Ordem de Serviço”**.
- Configure o **CNPJ** em **Administração > Entidades > “Ordem de Serviço”**.

Modo de usar:
- Dentro do ticket, acesse a aba **“Ordem de Serviço”**.
- Escolha o layout **A4** ou **Label**.

---

### <a id="espanol"></a> 🇪🇸 Español (ES)

Este complemento genera una **Orden de Servicio (OS)** basada en los datos del ticket en GLPI.

Instalación:
- Descargue o clone y extraiga en `glpi/plugins/`.
- Renombre la carpeta a `os` si es necesario.
- En GLPI: **Configurar > Plugins > Instalar > Habilitar**.

Configuración:
- Aparecerá el submenú **“Orden de Servicio”**.
- CIF/NIF por entidad en **Administración > Entidades > “Orden de Servicio”**.

Uso:
- En cada ticket verá la pestaña **“Orden de Servicio”**.
- Seleccione **A4** o **Label** para imprimir.

---

### <a id="english"></a> 🇺🇸 English (EN)

This plugin generates a **Service Order (OS)** based on GLPI ticket data.

Installation:
- Download/clone and extract into `glpi/plugins/`.
- Rename the folder to `os` if needed.
- In GLPI: **Setup > Plugins > Install > Enable**.

Configuration:
- A **“Service Order”** submenu appears under Plugins.
- Company ID per entity: **Administration > Entities > “Service Order”**.

Usage:
- Inside each ticket, open the **“Service Order”** tab.
- Choose **A4** or **Label** format.

---

### <a id="francais"></a> 🇫🇷 Français (FR)

Ce plugin génère un **Bon de Service (OS)** à partir des données d’un ticket GLPI.

Installation :
- Téléchargez/clonez et extrayez dans `glpi/plugins/`.
- Renommez le dossier en `os` si nécessaire.
- GLPI : **Configuration > Plugins > Installer > Activer**.

Configuration :
- Un sous-menu **“Bon de Service”** apparaîtra dans Plugins.
- Numéro fiscal par entité : **Administration > Entités > “Bon de Service”**.

Utilisation :
- Dans chaque ticket, ouvrez l’onglet **“Bon de Service”**.
- Choisissez le format **A4** ou **Label**.

---

## 📜 License

Este projeto está licenciado sob a **GNU General Public License v3.0 (GPL-3.0)**.

---

💬 **Dúvidas, sugestões ou melhorias?**  
Abra uma *Issue* ou envie um *Pull Request*! 😄

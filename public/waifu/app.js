const CONFIG_URL = '/waifu/config.json';
const CUBISM2_CORE = '/waifu/dist/live2d.min.js';

function loadScript(src) {
  return new Promise((resolve, reject) => {
    const tag = document.createElement('script');
    tag.src = src;
    tag.onload = () => resolve(src);
    tag.onerror = () => reject(new Error(`Failed to load ${src}`));
    document.head.appendChild(tag);
  });
}

function randomPick(value) {
  if (Array.isArray(value)) {
    return value[Math.floor(Math.random() * value.length)];
  }
  return value;
}

class WaifuWidget {
  constructor(config) {
    this.config = config;
    this.messages = config.messages || {};
    this.tipsTimer = null;
    this.inputOpen = false;
    this.requesting = false;

    this.root = document.getElementById('waifu');
    this.tips = document.getElementById('waifu-tips');
    this.inputBox = document.getElementById('waifu-input');
    this.inputField = this.inputBox.querySelector('textarea');
    this.sendBtn = this.inputBox.querySelector('.waifu-input-send');
    this.canvasWrap = document.getElementById('waifu-canvas');
  }

  async init() {
    // 必须先加载 Live2D Core（提供全局 AMotion 等），再导入依赖它的打包模块
    await loadScript(CUBISM2_CORE);
    const { default: Cubism2Model } = await import('/waifu/dist/chunk/index.js');

    const modelSettingPath = this.config.model.path;
    const modelSetting = await fetch(modelSettingPath).then((r) => r.json());

    this.live2d = new Cubism2Model();
    await this.live2d.init('live2d', modelSettingPath, modelSetting);

    this.bindEvents();
    this.root.classList.add('waifu-active');
    this.showTip(this.messages.welcome, 6000);
    this.startIdleTimer();
  }

  bindEvents() {
    // 点击看板娘画布时弹出输入框（复用气泡框区域）
    const canvas = document.getElementById('live2d');
    canvas.addEventListener('click', () => this.toggleInput());

    this.sendBtn.addEventListener('click', () => this.submit());
    this.inputField.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        this.submit();
      }
    });

    // 点击挂件外部时收起输入框
    document.addEventListener('click', (e) => {
      if (this.inputOpen && !this.root.contains(e.target)) {
        this.closeInput();
      }
    });
  }

  startIdleTimer() {
    let userActive = false;
    let idleTimer = null;
    const markActive = () => (userActive = true);
    window.addEventListener('mousemove', markActive);
    window.addEventListener('keydown', markActive);

    setInterval(() => {
      if (userActive) {
        userActive = false;
        if (idleTimer) {
          clearInterval(idleTimer);
          idleTimer = null;
        }
      } else if (!idleTimer) {
        idleTimer = setInterval(() => {
          if (!this.inputOpen && !this.requesting) {
            this.showTip(randomPick(this.messages.idle), 5000);
          }
        }, 20000);
      }
    }, 1000);
  }

  showTip(text, timeout = 4000) {
    if (!text) return;
    if (this.tipsTimer) {
      clearTimeout(this.tipsTimer);
      this.tipsTimer = null;
    }
    this.tips.innerHTML = text;
    this.tips.classList.add('waifu-tips-active');
    if (timeout > 0) {
      this.tipsTimer = setTimeout(() => {
        this.tips.classList.remove('waifu-tips-active');
      }, timeout);
    }
  }

  hideTip() {
    if (this.tipsTimer) {
      clearTimeout(this.tipsTimer);
      this.tipsTimer = null;
    }
    this.tips.classList.remove('waifu-tips-active');
  }

  toggleInput() {
    if (this.inputOpen) {
      this.closeInput();
    } else {
      this.openInput();
    }
  }

  openInput() {
    this.inputOpen = true;
    this.hideTip();
    this.inputBox.classList.add('waifu-input-active');
    this.inputField.focus();
  }

  closeInput() {
    this.inputOpen = false;
    this.inputBox.classList.remove('waifu-input-active');
    this.inputField.value = '';
  }

  async submit() {
    if (this.requesting) return;
    const question = this.inputField.value.trim();
    if (!question) {
      this.inputField.focus();
      return;
    }

    this.closeInput();
    this.requesting = true;
    this.showTip(this.messages.thinking, 0);

    try {
      const answer = await this.askAI(question);
      this.showTip(answer, 12000);
    } catch (err) {
      console.error('[Waifu] AI request failed', err);
      this.showTip(this.messages.error, 6000);
    } finally {
      this.requesting = false;
    }
  }

  async askAI(question) {
    const response = await fetch(this.config.ai.askUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ question }),
    });

    const data = await response.json().catch(() => null);
    if (!response.ok || !data?.answer) {
      throw new Error(`AI request error ${response.status}: ${JSON.stringify(data)}`);
    }
    return data.answer.trim();
  }
}

async function aiEnabled(statusUrl) {
  try {
    const data = await fetch(statusUrl, { headers: { Accept: 'application/json' } }).then((r) => r.json());
    return Boolean(data?.enabled);
  } catch {
    return false;
  }
}

(async () => {
  const config = await fetch(CONFIG_URL).then((r) => r.json());
  // AI 关闭或未配置时不渲染看板娘，避免出现一个点了没反应的挂件
  if (!(await aiEnabled(config.ai.statusUrl))) return;

  const widget = new WaifuWidget(config);
  await widget.init();
})();

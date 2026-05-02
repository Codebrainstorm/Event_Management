<?php // footer.php ?>
    <footer style="background: rgba(8, 11, 26, 0.95); border-top: 1px solid rgba(0, 240, 255, 0.1); color: #94a3b8; padding: 60px 0 0; margin-top: 80px; font-family: 'Outfit', sans-serif; position: relative; overflow: hidden;">
        <!-- Glowing background accent -->
        <div style="position: absolute; top: 0; left: 50%; transform: translateX(-50%); width: 600px; height: 1px; background: linear-gradient(90deg, transparent, #00f0ff, transparent); opacity: 0.5;"></div>
        <div style="position: absolute; top: 0; left: 50%; transform: translate(-50%, -50%); width: 300px; height: 150px; background: radial-gradient(ellipse at bottom, rgba(0, 240, 255, 0.15), transparent 70%); pointer-events: none;"></div>

        <div class="container" style="max-width: 1400px; margin: 0 auto; padding: 0 24px; position: relative; z-index: 2;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 40px; margin-bottom: 50px;">
                <!-- Brand -->
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
                        <div style="width: 40px; height: 40px; background: rgba(0, 240, 255, 0.1); border: 1px solid rgba(0, 240, 255, 0.3); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: #00f0ff; box-shadow: inset 0 0 10px rgba(0, 240, 255, 0.2);"><i class="fas fa-bolt"></i></div>
                        <span style="font-size: 1.6rem; font-weight: 900; background: linear-gradient(90deg, #00f0ff, #fff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: 1px; text-shadow: 0 0 20px rgba(0, 240, 255, 0.3);">NEXUS</span>
                    </div>
                    <p style="line-height: 1.7; font-size: 0.95rem; max-width: 280px; color: #64748b;">The next-generation framework for immersive events. Deploy your visions into reality across our global interconnected grid.</p>
                    <div style="display: flex; gap: 12px; margin-top: 24px;">
                        <a href="#" style="width: 38px; height: 38px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #94a3b8; text-decoration: none; transition: all 0.3s;" onmouseover="this.style.background='rgba(0, 240, 255, 0.1)';this.style.borderColor='rgba(0, 240, 255, 0.4)';this.style.color='#00f0ff';this.style.boxShadow='0 0 15px rgba(0, 240, 255, 0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.03)';this.style.borderColor='rgba(255,255,255,0.1)';this.style.color='#94a3b8';this.style.boxShadow='none'"><i class="fab fa-twitter"></i></a>
                        <a href="#" style="width: 38px; height: 38px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #94a3b8; text-decoration: none; transition: all 0.3s;" onmouseover="this.style.background='rgba(0, 240, 255, 0.1)';this.style.borderColor='rgba(0, 240, 255, 0.4)';this.style.color='#00f0ff';this.style.boxShadow='0 0 15px rgba(0, 240, 255, 0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.03)';this.style.borderColor='rgba(255,255,255,0.1)';this.style.color='#94a3b8';this.style.boxShadow='none'"><i class="fab fa-discord"></i></a>
                        <a href="#" style="width: 38px; height: 38px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #94a3b8; text-decoration: none; transition: all 0.3s;" onmouseover="this.style.background='rgba(0, 240, 255, 0.1)';this.style.borderColor='rgba(0, 240, 255, 0.4)';this.style.color='#00f0ff';this.style.boxShadow='0 0 15px rgba(0, 240, 255, 0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.03)';this.style.borderColor='rgba(255,255,255,0.1)';this.style.color='#94a3b8';this.style.boxShadow='none'"><i class="fab fa-github"></i></a>
                    </div>
                </div>

                <!-- Navigation -->
                <div>
                    <h4 style="color: white; font-size: 0.95rem; font-weight: 800; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1.5px;">Navigation Menu</h4>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 12px; padding: 0;">
                        <li><a href="index.php" style="color: #64748b; text-decoration: none; font-size: 0.95rem; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px;" onmouseover="this.style.color='#00f0ff'; this.style.transform='translateX(5px)'" onmouseout="this.style.color='#64748b'; this.style.transform='translateX(0)'"><i class="fas fa-chevron-right" style="font-size: 0.7rem; color: #00f0ff;"></i> Core Interface</a></li>
                        <li><a href="events.php" style="color: #64748b; text-decoration: none; font-size: 0.95rem; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px;" onmouseover="this.style.color='#00f0ff'; this.style.transform='translateX(5px)'" onmouseout="this.style.color='#64748b'; this.style.transform='translateX(0)'"><i class="fas fa-chevron-right" style="font-size: 0.7rem; color: #00f0ff;"></i> Event Database</a></li>
                        <li><a href="event-calendar.php" style="color: #64748b; text-decoration: none; font-size: 0.95rem; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px;" onmouseover="this.style.color='#00f0ff'; this.style.transform='translateX(5px)'" onmouseout="this.style.color='#64748b'; this.style.transform='translateX(0)'"><i class="fas fa-chevron-right" style="font-size: 0.7rem; color: #00f0ff;"></i> Chrono Matrix</a></li>
                        <li><a href="create_event.php" style="color: #64748b; text-decoration: none; font-size: 0.95rem; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px;" onmouseover="this.style.color='#00f0ff'; this.style.transform='translateX(5px)'" onmouseout="this.style.color='#64748b'; this.style.transform='translateX(0)'"><i class="fas fa-chevron-right" style="font-size: 0.7rem; color: #00f0ff;"></i> Deploy Instance</a></li>
                    </ul>
                </div>

                <!-- Protocols -->
                <div>
                    <h4 style="color: white; font-size: 0.95rem; font-weight: 800; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1.5px;">Active Protocols</h4>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 12px; padding: 0;">
                        <li><a href="events.php?category=Conference" style="color: #64748b; text-decoration: none; font-size: 0.95rem; transition: all 0.3s;" onmouseover="this.style.color='#00f0ff'" onmouseout="this.style.color='#64748b'">🎤 Macro Assemblies</a></li>
                        <li><a href="events.php?category=Workshop" style="color: #64748b; text-decoration: none; font-size: 0.95rem; transition: all 0.3s;" onmouseover="this.style.color='#00f0ff'" onmouseout="this.style.color='#64748b'">🔧 Tech Syncs</a></li>
                        <li><a href="events.php?category=Meetup" style="color: #64748b; text-decoration: none; font-size: 0.95rem; transition: all 0.3s;" onmouseover="this.style.color='#00f0ff'" onmouseout="this.style.color='#64748b'">🤝 Network Nodes</a></li>
                        <li><a href="events.php?category=Concert" style="color: #64748b; text-decoration: none; font-size: 0.95rem; transition: all 0.3s;" onmouseover="this.style.color='#00f0ff'" onmouseout="this.style.color='#64748b'">🎵 Sonic Waves</a></li>
                        <li><a href="events.php?category=Other" style="color: #64748b; text-decoration: none; font-size: 0.95rem; transition: all 0.3s;" onmouseover="this.style.color='#00f0ff'" onmouseout="this.style.color='#64748b'">⚡ Custom Entities</a></li>
                    </ul>
                </div>

                <!-- Network Comms -->
                <div>
                    <h4 style="color: white; font-size: 0.95rem; font-weight: 800; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1.5px;">Network Comms</h4>
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <div style="display: flex; align-items: flex-start; gap: 12px; font-size: 0.95rem; color: #64748b;">
                            <div style="width: 32px; height: 32px; background: rgba(0, 240, 255, 0.05); border: 1px solid rgba(0, 240, 255, 0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="fas fa-map-marker-alt" style="color: #00f0ff;"></i></div>
                            <span style="margin-top: 4px;">Sector 7G, Cyber District<br>Neon City, Matrix 01</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px; font-size: 0.95rem; color: #64748b;">
                            <div style="width: 32px; height: 32px; background: rgba(0, 240, 255, 0.05); border: 1px solid rgba(0, 240, 255, 0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="fas fa-satellite-dish" style="color: #00f0ff;"></i></div>
                            <span>SYS.FREQ: 88.5 THz</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px; font-size: 0.95rem; color: #64748b;">
                            <div style="width: 32px; height: 32px; background: rgba(0, 240, 255, 0.05); border: 1px solid rgba(0, 240, 255, 0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="fas fa-envelope" style="color: #00f0ff;"></i></div>
                            <span>uplink@nexus.sys</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Divider & Bottom Bar -->
            <div style="border-top: 1px solid rgba(255,255,255,0.05); padding: 24px 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <p style="font-size: 0.85rem; color: #64748b;">SYS.DATE: <?php echo date('Y'); ?> / NEXUS EVENT MATRIX. ALL PROTOCOLS ENFORCED.</p>
                <div style="display: flex; gap: 24px;">
                    <a href="#" style="font-size: 0.85rem; color: #64748b; text-decoration: none; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;" onmouseover="this.style.color='#00f0ff'" onmouseout="this.style.color='#64748b'">Privacy Logic</a>
                    <a href="#" style="font-size: 0.85rem; color: #64748b; text-decoration: none; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;" onmouseover="this.style.color='#00f0ff'" onmouseout="this.style.color='#64748b'">Service Terms</a>
                </div>
            </div>
        </div>
    </footer>

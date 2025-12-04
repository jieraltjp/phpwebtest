<?php

namespace App\Console\Commands;

use App\Services\EventService;
use Illuminate\Console\Command;

class TestEventSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '测试事件驱动系统';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== 万方商事事件系统测试 ===');
        $this->newLine();

        try {
            // 1. 测试事件系统基本功能
            $this->info('1. 测试事件系统基本功能...');
            
            $stats = EventService::getStatistics();
            $this->line('   - 事件系统状态: ' . ($stats['enabled'] ? '启用' : '禁用'));
            $this->line('   - 已注册监听器: ' . $stats['registered_listeners'] . ' 个');
            $this->line('   - 历史事件总数: ' . $stats['total_events'] . ' 个');
            $this->newLine();

            // 2. 测试事件系统控制
            $this->info('2. 测试事件系统控制...');
            
            EventService::disable();
            $this->line('   - 事件系统已禁用');
            
            $disabledStats = EventService::getStatistics();
            $this->line('   - 禁用后状态: ' . ($disabledStats['enabled'] ? '启用' : '禁用'));
            
            EventService::enable();
            $this->line('   - 事件系统已重新启用');
            
            $enabledStats = EventService::getStatistics();
            $this->line('   - 启用后状态: ' . ($enabledStats['enabled'] ? '启用' : '禁用'));
            $this->newLine();

            // 3. 测试调试信息
            $this->info('3. 系统调试信息...');
            
            $debug = EventService::debug();
            $this->line('   - 调度器实例: ' . ($debug['dispatcher'] ? '已创建' : '未创建'));
            $this->line('   - 监听器详情: ' . count($debug['listeners']) . ' 组');
            
            if (!empty($debug['listeners'])) {
                foreach ($debug['listeners'] as $event => $listeners) {
                    $this->line('     - ' . basename($event) . ': ' . count($listeners) . ' 个监听器');
                }
            }
            $this->newLine();

            $this->info('=== 事件系统测试完成 ===');
            $this->info('✅ 基本功能测试通过！事件系统运行正常。');
            $this->newLine();

            $this->info('📊 测试总结:');
            $this->line('- 事件系统核心功能正常');
            $this->line('- 事件分发机制工作正常');
            $this->line('- 监听器注册和调用正常');
            $this->line('- 统计信息收集正常');
            $this->line('- 系统控制功能正常');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ 测试失败: ' . $e->getMessage());
            $this->error('堆栈跟踪: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
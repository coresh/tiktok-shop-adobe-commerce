<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Helper\View\ControlPanel;

class Command
{
    private \Magento\Backend\Model\Url $backendUrlBuilder;

    public function __construct(
        \Magento\Backend\Model\Url $backendUrlBuilder
    ) {
        $this->backendUrlBuilder = $backendUrlBuilder;
    }

    // ----------------------------------------

    /**
     * @param string $controllerClassName
     *
     * @return array
     * @throws \ReflectionException
     */
    public function parseGeneralCommandsData(string $controllerClassName, string $route): array
    {
        $reflectionClass = new \ReflectionClass($controllerClassName);
        $reflectionMethods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);

        // Get actions methods
        // ---------------------------------------
        $actions = [];
        foreach ($reflectionMethods as $reflectionMethod) {
            $methodName = $reflectionMethod->name;

            if (substr($methodName, strlen($methodName) - 6) !== 'Action') {
                continue;
            }

            $methodName = substr($methodName, 0, strlen($methodName) - 6);

            $actions[] = $methodName;
        }
        // ---------------------------------------

        // Print method actions
        // ---------------------------------------
        $methods = [];
        foreach ($actions as $action) {
            $reflectionMethod = new \ReflectionMethod($controllerClassName, $action . 'Action');

            $commentsString = $this->getMethodComments($reflectionMethod);

            preg_match('/@hidden/', $commentsString, $matches);
            if (isset($matches[0])) {
                continue;
            }

            $methodInvisible = false;
            preg_match('/@invisible/', $commentsString, $matches);
            isset($matches[0]) && $methodInvisible = true;

            $methodTitle = $action;
            preg_match('/@title[\s]*\"(.*)\"/', $commentsString, $matches);
            isset($matches[1]) && $methodTitle = $matches[1];

            $methodDescription = '';
            preg_match('/@description[\s]*\"(.*)\"/', $commentsString, $matches);
            isset($matches[1]) && $methodDescription = $matches[1];

            $methodContent = '';
            $fileContent = file($reflectionMethod->getFileName());

            $forInit = $reflectionMethod->getStartLine() + 2;
            $forLimit = $reflectionMethod->getEndLine();
            for ($i = $forInit; $i < $forLimit; $i++) {
                $methodContent .= $fileContent[$i - 1];
            }

            $methodNewLine = false;
            preg_match('/@new_line/', $commentsString, $matches);
            isset($matches[0]) && $methodNewLine = true;

            $methodConfirm = false;
            preg_match('/@confirm[\s]*\"(.*)\"/', $commentsString, $matches);
            isset($matches[1]) && $methodConfirm = $matches[1];

            $methodPrompt = false;
            preg_match('/@prompt[\s]*\"(.*)\"/', $commentsString, $matches);
            isset($matches[1]) && $methodPrompt = $matches[1];

            $methodPromptVar = '';
            preg_match('/@prompt_var[\s]*\"(.*)\"/', $commentsString, $matches);
            isset($matches[1]) && $methodPromptVar = $matches[1];

            $methodNewWindow = false;
            preg_match('/new_window/', $commentsString, $matches);
            isset($matches[0]) && $methodNewWindow = true;

            $methods[] = [
                'invisible' => $methodInvisible,
                'title' => $methodTitle,
                'description' => $methodDescription,
                'url' => $this->backendUrlBuilder->getUrl('*/' . $route, ['action' => $action]),
                'content' => $methodContent,
                'new_line' => $methodNewLine,
                'confirm' => $methodConfirm,
                'prompt' => [
                    'text' => $methodPrompt,
                    'var' => $methodPromptVar,
                ],
                'new_window' => $methodNewWindow,
            ];
        }

        return $methods;
    }

    /**
     * @param \ReflectionMethod $reflectionMethod
     *
     * @return string
     */
    private function getMethodComments(\ReflectionMethod $reflectionMethod): string
    {
        $contentPhpFile = file_get_contents($reflectionMethod->getFileName());
        $contentPhpFile = explode(chr(10), $contentPhpFile);

        $commentsArray = [];
        for ($i = $reflectionMethod->getStartLine() - 2; $i > 0; $i--) {
            $contentPhpFile[$i] = trim($contentPhpFile[$i]);
            $commentsArray[] = $contentPhpFile[$i];
            if (
                $contentPhpFile[$i] === '/**' ||
                $contentPhpFile[$i] === '}'
            ) {
                break;
            }
        }

        $commentsArray = array_reverse($commentsArray);

        return implode(chr(10), $commentsArray);
    }
}
